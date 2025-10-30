# hook — minimal PECL-style PHP extension

Exposes:

- `hook_world(): string` → returns "`<greeting> from PECL!`"
- `hook_repeat(string $name, int $times = 1): string`

Configurable INI:

- `hook.greeting` (default: `Hello`)

## Build (Linux/macOS)

```bash
phpize
./configure --enable-hook
make -j$(getconf _NPROCESSORS_ONLN)
sudo make install

# enable
echo "extension=hook" | sudo tee /etc/php/*/mods-available/hook.ini >/dev/null || true
# OR add to your php.ini; optionally: hook.greeting = "Hello"

php -m | grep hook
php -r 'var_dump(hook_world()); echo PHP_EOL; echo hook_repeat("World", 2), PHP_EOL;'
```

## Windows (quick)

Use the PHP SDK + matching Visual Studio. Run `buildconf`, `configure --enable-hook`, then `nmake`. Copy the built DLL into `ext` and enable in `php.ini`.

## Test
```bash
php -d extension=modules/hook.so -n -d hook.greeting=Howdy -r 'echo hook_repeat("Farid",3),PHP_EOL;'
# or run: make test
```
