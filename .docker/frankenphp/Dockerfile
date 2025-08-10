FROM dunglas/frankenphp:1.9-php8-alpine

# 启用 PHP 生产配置
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# 在此处添加其他扩展：
RUN install-php-extensions \
	Core \
	date \
	libxml \
	openssl \
	pcre \
	sqlite3 \
	zlib \
	bcmath \
	ctype \
	curl \
	dom \
	filter \
	ftp \
	gd \
	gettext \
	hash \
	iconv \
	intl \
	json \
	mbstring \
	SPL \
	session \
	pcntl \
	standard \
	PDO \
	mysqlnd \
	pdo_sqlite \
	Phar \
	posix \
	Reflection \
	pdo_mysql \
	shmop \
	SimpleXML \
	soap \
	sockets \
	sodium \
	mysqli \
	sysvsem \
	tokenizer \
	xml \
	xmlreader \
	xmlwriter \
	zip \
	fileinfo \
	igbinary \
	redis \
	exif 

# 复制项目
COPY . /app