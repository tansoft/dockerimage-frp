
#docker build -t frpmapcli -f Dockerfile.client . --build-arg FRP_TOKEN=password --build-arg FRP_SERVER_ADDR=1.2.3.4
# --build-arg FRP_SERVER_PORT=443 --build-arg FRP_ADMIN_PORT=7400

docker rm -f -v frpmapcli

docker run -d --net=host --restart=always --name "frpmapcli" frpmapcli

docker logs -f frpmapcli
#docker exec -it frpmapcli /bin/sh

