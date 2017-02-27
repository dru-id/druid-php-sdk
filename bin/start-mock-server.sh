#!/bin/bash

HTTP_MOCK_SERVER=${PWD}/../vendor/upscale/http-server-mock/server.php
FINAL_CONFIG_FILE=${PWD}/../vendor/upscale/http-server-mock/config.json
CUSTOM_CONFIG_FILE=${PWD}/../tests/http-server-mock-config.json

if [ ! -f $FINAL_CONFIG_FILE ]; then
    ln -s ${CUSTOM_CONFIG_FILE} ${FINAL_CONFIG_FILE}
fi

php -S 127.0.0.1:8083 $HTTP_MOCK_SERVER