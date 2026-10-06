load("//anybuild/tools:laravel.bzl", "laravel_config", "laravel_build", "laravel_serve")

config = laravel_config(
    schema = 1,
    commands = {
        "start": "php -S 0.0.0.0:$PORT -t public",
    },
)

build = laravel_build(config)

laravel_serve(
    config,
    build,
    name = "keuangan",
)
