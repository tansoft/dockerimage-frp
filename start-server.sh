
#docker build -t frpmap -f Dockerfile.server . --build-arg FRP_TOKEN=password
# --build-arg FRP_SERVER_ADDR=1.2.3.4 --build-arg FRP_SERVER_PORT=443 --build-arg FRP_ADMIN_PORT=7400
# FRP_ADMIN_PORT 只是对内

docker rm -f -v frpmap

docker run -d -p 443:443 --restart=always --name "frpmap" frpmap

#docker logs -f frpmap
#docker exec -it frpmap /bin/sh

