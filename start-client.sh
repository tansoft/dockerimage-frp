#!/bin/sh

docker rm -f -v frpmapcli

docker run -d --net=host --restart=always --name "frpmapcli" frpmapcli

docker logs -f frpmapcli
#docker exec -it frpmapcli /bin/sh

