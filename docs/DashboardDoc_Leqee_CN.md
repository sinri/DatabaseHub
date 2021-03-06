# 欢迎使用 DatabaseHub for Leqee

## 用户体系

已接入Account Auth V3的CAS系统，可以使用钉钉扫码使用该系统。
如果遇到登录问题，请在联系人事部之前先尝试利用钉钉上的乐其OC应用自助解决。

需要权限可通过OC系统发起运维申请，并经上级审批。

## 功能说明

### 数据申请的递交、审批和执行

- 数据导出(READ)：执行单条查询语句（Select/Show/Explain）以导出CSV数据。
- 数据更新(MODIFY)：执行多条更新语句（Update/Insert/Delete/Replace），可反馈执行效果。
- 预设执行(EXECUTE)：执行存储过程语句（CALL），但不提供返回结果。
- 结构变更(DDL)：执行DDL以及TRUNCATE语句。

### 快速查询

* 快速查询仅对有权限的用户开放。
* 快速查询仅支持导出类请求，并限制了返回行数。
* 其执行时间受PHP网络服务器的最长执行时间限制，请保证所进行的查询是快速的。

### 特别注意

每次申请SQL总长度不超过 2<sup>24</sup> 字节，包括可见和不可见字符。

申请结构变更时，需要新建和变更表、函数或存储过程，不要忘记必要的 `DROP IF EXISTS` 。

如新建函数和存储过程等，不需要声明和恢复 `DELIMITER` 语句，在 `BEGIN` 和 `END` 之间正常使用`;`即可。

执行存储过程的 `CALL` 类型为 EXECUTE 。

> phpMyAdmin的校验库有[迷之问题](https://github.com/phpmyadmin/sql-parser/issues/223)，用`CALL x();`的形式苟且偷生，没有括号不行。

关于 `TRUNCATE`，类型归类到了 DDL 。
> phpMyAdmin的校验库有[迷之问题](https://github.com/phpmyadmin/sql-parser/issues/221)，必须大写才行。

目前已经支持查询阿里云的分析型数据库查询，受其产品实现限制，请认真阅读该产品说明文档，如有问题可向阿里云产品支持咨询。

## About

This project is maintained by [Sinri](https://github.com/sinri) , [Vinci](https://github.com/RoamIn) and [Caroltc](https://github.com/caroltc).

DatabaseHub is licensed under the GNU General Public License v3.0.

Lord, have mercy on us. 2019 Jan 16th.