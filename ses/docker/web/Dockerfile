FROM eboraas/apache-php

RUN apt-get update

RUN apt-get install -y ca-certificates

ADD 000-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

RUN chgrp -R www-data /var/www/html

RUN chmod -R g+w /var/www/html