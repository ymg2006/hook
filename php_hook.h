#ifndef PHP_HOOK_H
#define PHP_HOOK_H

#include "php.h"
#include "zend_smart_str.h"

#define PHP_HOOK_EXTNAME "hook"
#define PHP_HOOK_VERSION "0.1.0"

extern zend_module_entry hook_module_entry;
#define phpext_hook_ptr &hook_module_entry

/* Module globals */
ZEND_BEGIN_MODULE_GLOBALS(hook)
    zend_bool enable;
ZEND_END_MODULE_GLOBALS(hook)

ZEND_EXTERN_MODULE_GLOBALS(hook)

#ifdef ZTS
#include "TSRM.h"
#define HOOK_G(v) ZEND_MODULE_GLOBALS_ACCESSOR(hook, v)
#else
#define HOOK_G(v) ZEND_MODULE_GLOBALS_ACCESSOR(hook, v)
#endif

/* Functions */
PHP_FUNCTION(hook_world);
PHP_FUNCTION(hook_repeat);

#endif /* PHP_HOOK_H */
