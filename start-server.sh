#!/bin/sh

docker rm -f -v frpmap

docker run -d -p 443:443 --restart=always --name "frpmap" frpmap

#docker logs -f frpmap
#docker exec -it frpmap /bin/sh

