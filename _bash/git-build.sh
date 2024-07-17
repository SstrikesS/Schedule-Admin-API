#!/bin/bash

tag=dev;
distro=alpine;

while getopts "t:" flag
do
  # shellcheck disable=SC2220
  case "${flag}" in
    t) tag="${OPTARG}";;
  esac
done

docker buildx build -t registry.git.iig.vn/ii-slf/php-slf-api-amc:"$tag" -f Dockerfile-$distro --build-arg B_TAG="$tag" .
docker push registry.git.iig.vn/ii-slf/php-slf-api-amc:"$tag"

printf '%s\n%s\n' "[$(date '+%d/%m/%Y %H:%M:%S')] BUILD: $tag - AUTHOR: $(git config user.name) <$(git config user.email)>" "$(cat _bash/build.log)" > _bash/build.log
