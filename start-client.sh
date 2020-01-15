#!/bin/sh

docker rm -f -v frpmapcli

#https://windmt.com/2019/08/30/docker-for-mac-network/
docker run -d --privileged --network=host --restart=always --name "frpmapcli" frpmapcli

docker logs -f frpmapcli
#docker exec -it frpmapcli /bin/sh

