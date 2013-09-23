Этот скрипт предназначен для использования сервиса гео-локации ipgeobase.ru
на PHP. Ipgeobase.ru предоставляет подробную информацию по IP-адресу: город,
регион, федеральный округ, координаты - по городам России и Украины.
По этим странам сервис работает точнее MaxMind GeoIP.

Владислав Росс vladislav.ross@gmail.com

=======================================

В данном форке добавлена возможность выбирать кодировку выводимых данных,
а также добавлен composer.json, удалены файлы с данными и произведен небольшой рефакторинг.

Для пользователей composer:

```
"hundredminds/ipgeobase" : "1.02"
```

Чтобы не затруднять себя ручным обновлением файлов ipgeobase, рекомендуем настроить обновление через composer:

1. в секцию "repositories" добавить:

```
    {
        "type": "package",
        "package": {
            "name": "ipgeobase/data",
            "version": "1.0",
            "dist": {
                "url": "http://ipgeobase.ru/files/db/Main/geo_files.zip",
                "type": "zip"
            }
        }
    }
```
2. в секцию "required":

```
"ipgeobase/data" : "1.0"
```

Теперь, когда захотите обновить файлы ipgeobase - достаточно будет увеличить номер версии в обеих секциях и запустить composer update.

Разумеется, при этом придется передавать полный путь к файлам в конструктор:

```
$Gbase = new IPGeoBase(PROJECT_PATH . 'vendor/ipgeobase/data/cidr_optim.txt', PROJECT_PATH . 'vendor/ipgeobase/data/cities.txt');
```

=======================================

Для тех, кто не использует Composer:

1. Скачайте архив http://ipgeobase.ru/cgi-bin/Archive.cgi
   (хорошая идея настроить переодическое скачивание с помощью wget).
2. Распакуйте cidr_optim.txt и cities.txt.
2. Подключите ipgeobase.php.
3. Используйте класс IPGeoBase (см. example.php).
