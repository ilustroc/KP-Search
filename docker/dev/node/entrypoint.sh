#!/usr/bin/env sh
set -eu

if [ ! -x node_modules/.bin/vite ]; then
    npm ci
fi

exec "$@"
