FROM java:7

MAINTAINER Simeon Ackermann <amseon@web.de>


ENV JMETER_VERSION=2.13
ENV JMETER_HOME=/usr/local/apache-jmeter-${JMETER_VERSION}
ENV PATH=${JMETER_HOME}/bin:${PATH}
ENV DOCKERIZE_VERSION=0.2.0

RUN apt-get -y update && \
	apt-get -y install wget netcat

# install apache jmeter
RUN wget http://www.eu.apache.org/dist/jmeter/binaries/apache-jmeter-${JMETER_VERSION}.tgz && \
	tar -xzf apache-jmeter-${JMETER_VERSION}.tgz -C /usr/local/

# install dockerize
RUN wget https://github.com/jwilder/dockerize/releases/download/v${DOCKERIZE_VERSION}/dockerize-linux-amd64-v${DOCKERIZE_VERSION}.tar.gz
RUN tar -C /usr/local/bin -xzvf dockerize-linux-amd64-v${DOCKERIZE_VERSION}.tar.gz

RUN mkdir /jmeter

COPY jmeter.sh /jmeter/

WORKDIR /jmeter

CMD ["/bin/bash", "/jmeter/jmeter.sh"]