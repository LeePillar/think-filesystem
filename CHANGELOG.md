# 更新日志

## 1.0.4 (2026-11-13)

兼容性修复版本，无接口行为变更，升级无需修改调用方代码。

### PHP 8.4 / 8.5 兼容修复

- `Filesystem::disk()`：参数由隐式可空的 `string $name = null` 改为显式 `?string $name = null`，消除 PHP 8.4+ 的 `Implicitly marking parameter $name as nullable is deprecated` 弃用提示。
- `Filesystem::getConfig()`：同样改为显式 `?string $name = null`。
- `facade\Filesystem` 的 `@method` 注解同步改为 `?string`，并修正 `@mixin` 与 `use` 的命名空间（`filesystem\*` → `apixx\filesystem\*`）。
- 全包 9 个 PHP 文件在 PHP 8.5.12 + `error_reporting(E_ALL)` 下编译、加载均无弃用提示；未使用 PHP 8.1+ 独有语法，包内不存在其它 8.4/8.5 弃用写法（隐式可空参数、`${}` 插值、`utf8_encode`/`strftime` 等已废弃函数、非规范类型强转等均已逐项排查为 0 命中）。

### 元数据

- `composer.json` 新增 `"php": ">=8.0"` 运行时约束：代码仅使用 PHP 8.0 特性（联合类型等），向下兼容 PHP 8.0、8.1、8.2、8.3、8.4、8.5。
- 补充 `keywords`、完善 `description`；移除对库无效的 `minimum-stability` 字段（该字段只在根项目生效）。
- 版本号不在 `composer.json` 内声明，由 Git 标签 `v1.0.4` 决定（Composer 官方建议）。
- 新增 `topthink/framework: ^6.1 || ^8.0` 依赖声明（代码继承 `think\Manager`、`think\Service`）；补充 `LICENSE`（MIT 全文，与 `license` 字段一致）。

### 已知范围

- 四个驱动依赖 `apixx/flysystem-oss` 1.0.2、`apixx/flysystem-cos` 1.0.1、`apixx/flysystem-obs` 1.0.1、`apixx/flysystem-qiniu` 1.0.1 已同步完成兼容修复，`^1.0` 约束均可满足。
- 阿里云/腾讯云/七牛/华为云官方 SDK 自身仍存在隐式可空参数等 8.4+ 弃用写法，属第三方依赖，需上游修复或在项目侧收敛 `E_DEPRECATED`。
