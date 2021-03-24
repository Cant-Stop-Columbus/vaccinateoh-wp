FROM buckii/web-wordpress

ADD app.tar /app

ARG themeDirectory
ENV COMPOSE_PROJECT_NAME=$themeDirectory

RUN webpack-app-build.sh
