# Use MySQL 9.4.0 as the base
FROM mysql:9.4.0

# Apply custom configuration (innodb_use_native_aio=0 required for Docker compatibility)
COPY mysql.conf /etc/mysql/conf.d/custom.cnf

EXPOSE 3306
