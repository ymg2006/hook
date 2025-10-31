#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "ext/standard/info.h"
#include "php_hook.h"

/* Windows & MinHook */
#include <windows.h>
#include <wincrypt.h>
#include <ncrypt.h>
#include "minhook/include/MinHook.h"

/* -------------------- Globals -------------------- */
HMODULE g_Self = NULL;
HINSTANCE m_hinstbase = NULL;

static BOOL is_Loaded = FALSE;		  /* our LoadLibrary hooks installed */
static BOOL is_MHInitialized = FALSE; /* MinHook initialized */
static BOOL is_CryptVerifySignatureWHooked = FALSE;
static BOOL is_NCryptVerifySignatureHooked = FALSE;

/* -------------------- Debug helpers -------------------- */
static void DBG_A(const char *msg) { OutputDebugStringA(msg); }

/* -------------------- Helpers -------------------- */
static BOOL IsAdvapi32FilenameA(LPCSTR s)
{
	if (!s)
		return FALSE;
	const char *base = strrchr(s, '\\');
	base = base ? (base + 1) : s;
	return _stricmp(base, "advapi32.dll") == 0;
}
static BOOL IsAdvapi32FilenameW(LPCWSTR s)
{
	if (!s)
		return FALSE;
	const wchar_t *base = wcsrchr(s, L'\\');
	base = base ? (base + 1) : s;
	return _wcsicmp(base, L"advapi32.dll") == 0;
}

/* ========================================================
   NCryptVerifySignature hook
   ======================================================== */

typedef SECURITY_STATUS(WINAPI *OldNCryptVerifySignature)(
	NCRYPT_KEY_HANDLE hKey,
	VOID *pPaddingInfo,
	PBYTE pbHashValue,
	DWORD cbHashValue,
	PBYTE pbSignature,
	DWORD cbSignature,
	DWORD dwFlags);
static OldNCryptVerifySignature fpNCryptVerifySignature = NULL;

static SECURITY_STATUS WINAPI DetourNCryptVerifySignature(
	NCRYPT_KEY_HANDLE hKey,
	VOID *pPaddingInfo,
	PBYTE pbHashValue,
	DWORD cbHashValue,
	PBYTE pbSignature,
	DWORD cbSignature,
	DWORD dwFlags)
{
	DBG_A("[hook] NCryptVerifySignature called\n");
	/* Example: short-circuit success. Replace with original call if needed. */
	/* return fpNCryptVerifySignature(hKey, pPaddingInfo, pbHashValue, cbHashValue, pbSignature, cbSignature, dwFlags); */
	return ERROR_SUCCESS;
}

static BOOL InstallNCryptVerifySignatureHook(void)
{
	/* Prefer CreateHookApi to avoid forwarder issues */
	if (MH_CreateHookApi(L"ncrypt", L"NCryptVerifySignature",
						 (LPVOID)DetourNCryptVerifySignature,
						 (LPVOID *)&fpNCryptVerifySignature) == MH_OK)
	{
		if (MH_EnableHook(MH_ALL_HOOKS) == MH_OK)
		{
			DBG_A("[hook] Installed hook: NCryptVerifySignature (CreateHookApi)\n");
			return TRUE;
		}
		return FALSE;
	}

	/* Fallback if module isn't loaded yet */
	HMODULE hMod = GetModuleHandleA("ncrypt.dll");
	if (!hMod)
	{
		hMod = LoadLibraryA("ncrypt.dll");
		if (!hMod)
			return FALSE;
	}

	LPVOID targ = (LPVOID)GetProcAddress(hMod, "NCryptVerifySignature");
	if (!targ)
		return FALSE;

	if (MH_CreateHook(targ, (LPVOID)DetourNCryptVerifySignature,
					  (LPVOID *)&fpNCryptVerifySignature) != MH_OK)
		return FALSE;

	if (MH_EnableHook(targ) != MH_OK)
		return FALSE;

	DBG_A("[hook] Installed hook: NCryptVerifySignature (GetProcAddress)\n");
	return TRUE;
}

/* ========================================================
   CryptVerifySignatureW hook (Advapi32)
   ======================================================== */

typedef BOOL(WINAPI *OldCryptVerifySignatureW)(
	HCRYPTHASH hHash,
	const BYTE *pbSignature,
	DWORD dwSigLen,
	HCRYPTKEY hPubKey,
	LPCWSTR szDescription,
	DWORD dwFlags);
static OldCryptVerifySignatureW fpCryptVerifySignatureW = NULL;

static BOOL WINAPI DetourCryptVerifySignatureW(
	HCRYPTHASH hHash,
	const BYTE *pbSignature,
	DWORD dwSigLen,
	HCRYPTKEY hPubKey,
	LPCWSTR szDescription,
	DWORD dwFlags)
{
	DBG_A("[hook] CryptVerifySignatureW called\n");
	/* Example: short-circuit success. Replace with original call if needed. */
	/* return fpCryptVerifySignatureW(hHash, pbSignature, dwSigLen, hPubKey, szDescription, dwFlags); */
	return TRUE;
}

static BOOL InstallCryptVerifySignatureWHook(void)
{
	/* Prefer CreateHookApi */
	if (MH_CreateHookApi(L"advapi32", L"CryptVerifySignatureW",
						 (LPVOID)DetourCryptVerifySignatureW,
						 (LPVOID *)&fpCryptVerifySignatureW) == MH_OK)
	{
		if (MH_EnableHook(MH_ALL_HOOKS) == MH_OK)
		{
			DBG_A("[hook] Installed hook: CryptVerifySignatureW (CreateHookApi)\n");
			return TRUE;
		}
		return FALSE;
	}

	/* Fallback if module isn't loaded yet */
	HMODULE advapi = GetModuleHandleA("advapi32.dll");
	if (!advapi)
	{
		advapi = LoadLibraryA("advapi32.dll");
		if (!advapi)
			return FALSE;
	}

	LPVOID targ = (LPVOID)GetProcAddress(advapi, "CryptVerifySignatureW");
	if (!targ)
		return FALSE;

	if (MH_CreateHook(targ, (LPVOID)DetourCryptVerifySignatureW,
					  (LPVOID *)&fpCryptVerifySignatureW) != MH_OK)
		return FALSE;

	if (MH_EnableHook(targ) != MH_OK)
		return FALSE;

	DBG_A("[hook] Installed hook: CryptVerifySignatureW (GetProcAddress)\n");
	return TRUE;
}

/* ========================================================
   LoadLibrary* hooks – trigger our installers whenever advapi32 loads
   ======================================================== */

typedef HMODULE(WINAPI *TYPE_LoadLibraryA)(LPCSTR);
typedef HMODULE(WINAPI *TYPE_LoadLibraryExA)(LPCSTR, HANDLE, DWORD);
typedef HMODULE(WINAPI *TYPE_LoadLibraryW)(LPCWSTR);
typedef HMODULE(WINAPI *TYPE_LoadLibraryExW)(LPCWSTR, HANDLE, DWORD);

static TYPE_LoadLibraryA g_LoadLibraryA_original = NULL;
static TYPE_LoadLibraryExA g_LoadLibraryExA_original = NULL;
static TYPE_LoadLibraryW g_LoadLibraryW_original = NULL;
static TYPE_LoadLibraryExW g_LoadLibraryExW_original = NULL;

static void InstallCryptoIfAdvapiJustLoadedA(LPCSTR name)
{
	if (name && IsAdvapi32FilenameA(name))
	{
		DBG_A("[hook] ADVAPI32 loaded – installing crypto hooks\n");
		if (!is_CryptVerifySignatureWHooked)
			is_CryptVerifySignatureWHooked = InstallCryptVerifySignatureWHook();
		if (!is_NCryptVerifySignatureHooked)
			is_NCryptVerifySignatureHooked = InstallNCryptVerifySignatureHook();
	}
}
static void InstallCryptoIfAdvapiJustLoadedW(LPCWSTR name)
{
	if (name && IsAdvapi32FilenameW(name))
	{
		DBG_A("[hook] ADVAPI32 loaded – installing crypto hooks\n");
		if (!is_CryptVerifySignatureWHooked)
			is_CryptVerifySignatureWHooked = InstallCryptVerifySignatureWHook();
		if (!is_NCryptVerifySignatureHooked)
			is_NCryptVerifySignatureHooked = InstallNCryptVerifySignatureHook();
	}
}

static HMODULE WINAPI LoadLibraryA_replacement(LPCSTR lpFileName)
{
	HMODULE h = g_LoadLibraryA_original(lpFileName);
	InstallCryptoIfAdvapiJustLoadedA(lpFileName);
	return h;
}
static HMODULE WINAPI LoadLibraryExA_replacement(LPCSTR lpLibFileName, HANDLE hFile, DWORD dwFlags)
{
	HMODULE h = g_LoadLibraryExA_original(lpLibFileName, hFile, dwFlags);
	InstallCryptoIfAdvapiJustLoadedA(lpLibFileName);
	return h;
}
static HMODULE WINAPI LoadLibraryW_replacement(LPCWSTR lpFileName)
{
	HMODULE h = g_LoadLibraryW_original(lpFileName);
	InstallCryptoIfAdvapiJustLoadedW(lpFileName);
	return h;
}
static HMODULE WINAPI LoadLibraryExW_replacement(LPCWSTR lpLibFileName, HANDLE hFile, DWORD dwFlags)
{
	HMODULE h = g_LoadLibraryExW_original(lpLibFileName, hFile, dwFlags);
	InstallCryptoIfAdvapiJustLoadedW(lpLibFileName);
	return h;
}

static BOOL InstallLoadLibraryHook(void)
{
	/* Hook via CreateHookApi to handle kernel32/kernelbase forwarders */
	if (MH_CreateHookApi(L"kernel32", L"LoadLibraryA", (LPVOID)LoadLibraryA_replacement, (LPVOID *)&g_LoadLibraryA_original) != MH_OK)
		return FALSE;
	if (MH_CreateHookApi(L"kernel32", L"LoadLibraryExA", (LPVOID)LoadLibraryExA_replacement, (LPVOID *)&g_LoadLibraryExA_original) != MH_OK)
		return FALSE;
	if (MH_CreateHookApi(L"kernel32", L"LoadLibraryW", (LPVOID)LoadLibraryW_replacement, (LPVOID *)&g_LoadLibraryW_original) != MH_OK)
		return FALSE;
	if (MH_CreateHookApi(L"kernel32", L"LoadLibraryExW", (LPVOID)LoadLibraryExW_replacement, (LPVOID *)&g_LoadLibraryExW_original) != MH_OK)
		return FALSE;

	if (MH_EnableHook(MH_ALL_HOOKS) != MH_OK)
		return FALSE;
	DBG_A("[hook] LoadLibrary hooks installed\n");
	return TRUE;
}

/* ========================================================
   Module init helpers
   ======================================================== */

static void TryInstallCryptoHooksIfLoaded(void)
{
	HMODULE adv = GetModuleHandleA("advapi32.dll");
	if (adv && !is_CryptVerifySignatureWHooked)
	{
		is_CryptVerifySignatureWHooked = InstallCryptVerifySignatureWHook();
	}
	HMODULE ncr = GetModuleHandleA("ncrypt.dll");
	if (ncr && !is_NCryptVerifySignatureHooked)
	{
		is_NCryptVerifySignatureHooked = InstallNCryptVerifySignatureHook();
	}
}

static void Init(void)
{
	if (!HOOK_G(enable))
	{
		DBG_A("[hook] disabled via ini\n");
		return;
	}

	if (!is_MHInitialized)
	{
		is_MHInitialized = (MH_Initialize() == MH_OK);
		if (!is_MHInitialized)
		{
			DBG_A("[hook] MH_Initialize failed\n");
			return;
		}
		DBG_A("[hook] MH_Initialize ok\n");
	}

	if (!is_Loaded)
	{
		is_Loaded = InstallLoadLibraryHook();
	}

	/* If the targets are already present (very common), install now. */
	TryInstallCryptoHooksIfLoaded();
}

/* ========================================================
   INI entries & globals
   ======================================================== */
PHP_INI_BEGIN()
STD_PHP_INI_BOOLEAN("hook.enable", "1", PHP_INI_ALL, OnUpdateBool, enable, zend_hook_globals, hook_globals)
PHP_INI_END()

ZEND_DECLARE_MODULE_GLOBALS(hook)

static void php_hook_init_globals(zend_hook_globals *g)
{
	g->enable = 1;
}

/* ========================================================
   PHP functions
   ======================================================== */

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_hook_world, 0, 0, IS_STRING, 1)
ZEND_END_ARG_INFO()

PHP_FUNCTION(hook_world)
{
	zend_string *result = strpprintf(
		0,
		"status=%s; mh_initialized=%s; loadlib_hooks=%s; advapi_hook=%s; ncrypt_hook=%s",
		HOOK_G(enable) ? "enabled" : "disabled",
		is_MHInitialized ? "yes" : "no",
		is_Loaded ? "yes" : "no",
		is_CryptVerifySignatureWHooked ? "yes" : "no",
		is_NCryptVerifySignatureHooked ? "yes" : "no");
	RETURN_STR(result);
}

static const zend_function_entry hook_functions[] = {
	PHP_FE(hook_world, arginfo_hook_world)
		PHP_FE_END};

/* ========================================================
   MINIT / MSHUTDOWN / MINFO
   ======================================================== */

PHP_MINIT_FUNCTION(hook)
{
	ZEND_INIT_MODULE_GLOBALS(hook, php_hook_init_globals, NULL);
	REGISTER_INI_ENTRIES();
	Init();
#ifdef ZTS
	ZEND_TSRMLS_CACHE_UPDATE();
#endif
	return SUCCESS;
}

PHP_MSHUTDOWN_FUNCTION(hook)
{
	if (HOOK_G(enable))
	{
		if (is_Loaded)
		{
			MH_DisableHook(MH_ALL_HOOKS);
			is_Loaded = FALSE;

			is_CryptVerifySignatureWHooked = FALSE;
			is_NCryptVerifySignatureHooked = FALSE;
		}

		if (is_MHInitialized)
		{
			MH_Uninitialize();
			is_MHInitialized = FALSE;
		}
	}
	UNREGISTER_INI_ENTRIES();
	return SUCCESS;
}

PHP_MINFO_FUNCTION(hook)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "hook support", HOOK_G(enable) ? "enabled" : "disabled");
	php_info_print_table_row(2, "version", PHP_HOOK_VERSION);
	php_info_print_table_end();
	DISPLAY_INI_ENTRIES();
}

zend_module_entry hook_module_entry = {
	STANDARD_MODULE_HEADER,
	PHP_HOOK_EXTNAME,
	hook_functions,
	PHP_MINIT(hook),
	PHP_MSHUTDOWN(hook),
	NULL,
	NULL,
	PHP_MINFO(hook),
	PHP_HOOK_VERSION,
	STANDARD_MODULE_PROPERTIES};

#ifdef COMPILE_DL_HOOK
#ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
#endif
ZEND_GET_MODULE(hook)
#endif
