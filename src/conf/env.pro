# Framework
# ---------
YII_DEBUG   = false
YII_ENV     = pro

# Databases
# ---------
DB_DSN           =mysql:host=rm-wz9vb782539236zgs.mysql.rds.aliyuncs.com;port=3306;dbname=wx_admin_platform
DB_USERNAME      =wx_admin
DB_PASSWORD      =8A36m6yDjrlDpkaX
DB_TABLE_PREFIX  =

# Redis
# --------
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=6

# error handler
# --------
YII_ENABLE_ERROR_HANDLER =true

# 系统日志存放位置
LOG_FILE_PATH = '/mnt/wx_admin_platform/log/app.log'
LOG_CRON_FILE_PATH = '/mnt/wx_admin_platform/log/cron_app.log'

# 任务分发相关
LOCK_DIR = '/mnt/wx_admin_platform/log/'
LOCK_SUFFIX = '.lock'

# host info
SCHEME = 'http'
API_DOMAIN_INFO = 'admin-platform.kuvdm.cn'
FRONTEND_DOMAIN_INFO = 'static.kuvdm.cn'
CUSTOM_IMG_DOMAIN = 'http://img.kuvdm.cn'
CUSTOM_IMG_DOMAIN_REGEX = 'http:\/\/img.kuvdm.cn'
