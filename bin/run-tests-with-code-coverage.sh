#!/bin/bash

php ${PWD}/../vendor/bin/phpunit -c ${PWD}/../phpunit.xml --coverage-html ${PWD}/../tests/_output/coverage

