# TitleCache Docker

The Docker Image creates and fetchs a title-cache with Memcached (and more) for Virtuoso, with the power of Saft and Nette.


## Usage:

docker run -v $PWD/config.yml:/var/www/config/config.yml -v $PWD/results/var/www/results .

## Config:

Adopt the example `./TitleCache/example-config.yml`.

