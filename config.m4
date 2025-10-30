PHP_ARG_ENABLE(hook, whether to enable hook support,
[  --enable-hook           Enable hook support])

if test "$PHP_HOOK" != "no"; then
  AC_DEFINE(HAVE_HOOK, 1, [Have hook])
  PHP_NEW_EXTENSION(hook, hook.c, $ext_shared)
fi
