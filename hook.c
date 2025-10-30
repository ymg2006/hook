#ifdef HAVE_CONFIG_H
# include "config.h"
#endif

#include "php.h"
#include "ext/standard/info.h"
#include "php_hook.h"

/* INI entries */
PHP_INI_BEGIN()
    /* Boolean flag to enable/disable the hook */
    STD_PHP_INI_BOOLEAN("hook.enable", "1", PHP_INI_ALL,
        OnUpdateBool, enable, zend_hook_globals, hook_globals)
PHP_INI_END()

/* Declare the module globals storage */
ZEND_DECLARE_MODULE_GLOBALS(hook)

static void php_hook_init_globals(zend_hook_globals *g)
{
    /* default is also set by INI registration; keep a sane baseline */
    g->enable = 1;
}

/* arginfo: hook_world(): ?string */
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_hook_world, 0, 0, IS_STRING, 1 /* allow_null */)
ZEND_END_ARG_INFO()

/* hook_world(): returns "Hello from PECL!" when enabled; NULL when disabled */
PHP_FUNCTION(hook_world)
{
    if (!HOOK_G(enable)) {
        RETURN_NULL();
    }

    const char *greet = "Hello";
    size_t glen = strlen(greet);
    const char *suffix = " from PECL!";
    size_t slen = strlen(suffix);
    size_t len  = glen + slen;

    zend_string *result = zend_string_alloc(len, 0);
    memcpy(ZSTR_VAL(result), greet, glen);
    memcpy(ZSTR_VAL(result) + glen, suffix, slen);
    ZSTR_VAL(result)[len] = '\0';
    RETURN_STR(result);
}

/* Function list */
static const zend_function_entry hook_functions[] = {
    PHP_FE(hook_world,  arginfo_hook_world)
    PHP_FE_END
};

/* MINIT */
PHP_MINIT_FUNCTION(hook)
{
    ZEND_INIT_MODULE_GLOBALS(hook, php_hook_init_globals, NULL);
    REGISTER_INI_ENTRIES();
    return SUCCESS;
}

/* MSHUTDOWN */
PHP_MSHUTDOWN_FUNCTION(hook)
{
    UNREGISTER_INI_ENTRIES();
    return SUCCESS;
}

/* MINFO */
PHP_MINFO_FUNCTION(hook)
{
    php_info_print_table_start();
    php_info_print_table_header(2, "hook support", "enabled");
    php_info_print_table_row(2, "version", PHP_HOOK_VERSION);
    php_info_print_table_end();
    DISPLAY_INI_ENTRIES();
}

/* Module entry */
zend_module_entry hook_module_entry = {
    STANDARD_MODULE_HEADER,
    PHP_HOOK_EXTNAME,     /* name */
    hook_functions,       /* functions */
    PHP_MINIT(hook),      /* MINIT */
    PHP_MSHUTDOWN(hook),  /* MSHUTDOWN */
    NULL,                 /* RINIT */
    NULL,                 /* RSHUTDOWN */
    PHP_MINFO(hook),      /* MINFO */
    PHP_HOOK_VERSION,     /* version */
    STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_HOOK
# ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
# endif
ZEND_GET_MODULE(hook)
#endif
