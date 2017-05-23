#!/usr/bin/env python
# encoding: utf-8

import time
from fabric.api import (
    env, run, local, put, cd, settings, lcd
)
# from fabric.contrib.files import exists

app_name = "wx_admin_platform"

home_dir = '/home/deploy'
project_dir = home_dir + '/' + app_name
general_app_dir = '/mnt/%s' % app_name

source_dir = project_dir + ''
tmp_source_dir = '/tmp/%s/src' % app_name
log_dir = general_app_dir + '/log'
run_dir = general_app_dir + '/run'
cache_dir = general_app_dir + '/cache'
backup_dir = general_app_dir + '/' + 'backup'

worker_config = source_dir + '/worker_process/supervisord.conf'

worker_process_name = "wx_admin_platform_worker"
worker_process_name_news = "wx_admin_platform_worker_news"

current_conf = 'env.pro'


def product():
    """
    产品环境
    """
    global current_conf
    current_conf = 'env.pro'
    product_host = 'root@119.23.142.227:22'
    env.passwords = {
        product_host: "Dafyjk1024"
    }
    env.hosts = [product_host]

def product_new_one():
    """
    产品环境(02)
    """
    global current_conf
    current_conf = 'env.pronewone'
    product_host = 'root@139.196.31.106:22'
    env.passwords = {
        product_host: "#sCWeC^2"
    }
    env.hosts = [product_host]


def product_local():
    """
    产品环境
    """
    product_host = 'root@192.168.1.63:22'
    env.passwords = {
        product_host: "root"
    }
    env.hosts = [product_host]


def product_test():
    """
    产品开发测试环境
    """
    global current_conf
    current_conf = 'env.dev'
    product_host = 'root@120.24.17.53:22'
    env.passwords = {
        product_host: "Dafyjk1024"
    }
    env.hosts = [product_host]


def package_code():

    local('tar --exclude=./src/vendor --exclude=./src/runtime --exclude=./src/database --exclude=./src/tests -zcvf ./dist/{app_name}.tar.gz ./src'.format(app_name=app_name), capture=False)


def upload_remote():

    put('dist/%s.tar.gz' % app_name, '/tmp/%s.tar.gz' % app_name)

    with settings(warn_only=True):
        run('rm -fr /tmp/%s' % app_name)
        run('mkdir /tmp/%s' % app_name)

    with cd('/tmp/%s' % app_name):
        run('tar xzf /tmp/%s.tar.gz' % app_name)


def remove_remote_source():

    with settings(warn_only=True):
        run('rm -fr /home/deploy/%s' % app_name)


def update_remote_source():

    run('mv /tmp/%s/* /home/deploy/%s' % (app_name, app_name))


def prepare_base_dirs():

    # check if project dir exists
    run('mkdir -p %s' % project_dir)
    run('mkdir -p %s' % source_dir)
    run('mkdir -p %s' % backup_dir)
    run('mkdir -p %s' % run_dir)
    run('mkdir -p %s' % cache_dir)

    # for www php-fpm user reason
    with settings(warn_only=True):
        run('mkdir -p %s' % log_dir)


def prepare_setting():

    run('cd {source_dir}; ln -fs {source_dir}/conf/{current_conf} {source_dir}/.env'.format(current_conf=current_conf, source_dir=source_dir))


def install_vendor():

    run('cd {tmp_source_dir}; composer install --prefer-dist --no-dev --no-progress --optimize-autoloader;'.format(tmp_source_dir=tmp_source_dir))


def change_permission():

    run('chown -R www:www /home/deploy/%s' % app_name)
    run('chown -R www:www /mnt/%s' % app_name)
    run('chmod -R a+rwx /mnt/%s' % app_name)


def start_remote_worker():

    with settings(warn_only=True):
        run('supervisorctl -c %s shutdown' % worker_config)
        run('supervisord -c %s' % worker_config)
        time.sleep(1)

    run('supervisorctl -c %s start %s' % (worker_config, worker_process_name))
    run('supervisorctl -c %s start %s' % (worker_config, worker_process_name_news))


def stop_remote_worker():

    with settings(warn_only=True):
        run('supervisorctl -c %s stop %s' % (worker_config, worker_process_name))
        run('supervisorctl -c %s stop %s' % (worker_config, worker_process_name_news))


def deploy():

    # figure out the release name and version

    with lcd('./..'):
        package_code()
        upload_remote()
        install_vendor()

    # stop_remote_worker()
    prepare_base_dirs()
    remove_remote_source()
    update_remote_source()
    prepare_setting()
    change_permission()
    # start_remote_worker()
