FROM diegomarangoni/hhvm:cli
COPY . /usr/src/myapp
WORKDIR /usr/src/myapp
ENTRYPOINT [ "hhvm", "vendor/bin/phpunit" ]
CMD [ "--no-coverage" ]
