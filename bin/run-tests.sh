#!/bin/bash

ps cax | grep "start-mock" > /dev/null
if [ ! $? -eq 0 ]; then
  echo "Mock server not detected. Please start mock server running first 'start-mock-server.sh'."
  exit 1
fi

php ${PWD}/../vendor/bin/codecept --config=${PWD}/../tests/codeception.yml run unit,integration
