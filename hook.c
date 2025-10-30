#ifdef HAVE_CONFIG_H
# include "config.h"
#endif

#include "php.h"
#include "ext/standard/info.h"
#include "zend_smart_str.h"
#include "php_hook.h"

/* INI entries */
PHP_INI_BEGIN()
    STD_PHP_INI_ENTRY("hook.greeting", "Hello", PHP_INI_ALL, OnUpdateString, greeting, zend_hook_globals, hook_globals)
PHP_INI_END()

/* Declare the module globals storage */
ZEND_DECLARE_MODULE_GLOBALS(hook)

static void php_hook_init_globals(zend_hook_globals *g)
{
    g->greeting = NULL; /* value comes from INI; default set above */
}

/* arginfo */
ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_hook_world, 0, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_hook_repeat, 0, 1, IS_STRING, 0)
    ZEND_ARG_TYPE_INFO(0, name, IS_STRING, 0)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, times, IS_LONG, 0, "1")
ZEND_END_ARG_INFO()

/* hook_world(): returns "<greeting> from PECL!" */
PHP_FUNCTION(hook_world)
{
    const char *greet = HOOK_G(greeting) ? HOOK_G(greeting) : "Hello";
    size_t glen = strlen(greet);
    const char *suffix = " from PECL!";
    size_t len = glen + strlen(suffix);

    zend_string *result = zend_string_alloc(len, 0);
    memcpy(ZSTR_VAL(result), greet, glen);
    memcpy(ZSTR_VAL(result) + glen, suffix, strlen(suffix));
    ZSTR_VAL(result)[len] = '\\0';
    RETURN_STR(result);
}

/* hook_repeat(string $name, int $times = 1) */
PHP_FUNCTION(hook_repeat)
{
    zend_string *name;
    zend_long times = 1;

    ZEND_PARSE_PARAMETERS_START(1, 2)
        Z_PARAM_STR(name)
        Z_PARAM_OPTIONAL
        Z_PARAM_LONG(times)
    ZEND_PARSE_PARAMETERS_END();

    if (times < 1) times = 1;

    const char *greet = HOOK_G(greeting) ? HOOK_G(greeting) : "Hello";

    smart_str buf = {0};
    for (zend_long i = 0; i < times; i++) {
        smart_str_appends(&buf, greet);
        smart_str_appends(&buf, ", ");
        smart_str_append(&buf, name);
        if (i + 1 < times) {
            smart_str_appends(&buf, " | ");
        }
    }
    smart_str_0(&buf);

    if (buf.s) {
        RETURN_STR(buf.s);
    }
    RETURN_EMPTY_STRING();
}

/* Function list */
static const zend_function_entry hook_functions[] = {
    PHP_FE(hook_world,  arginfo_hook_world)
    PHP_FE(hook_repeat, arginfo_hook_repeat)
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
