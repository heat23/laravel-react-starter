<?php declare(strict_types = 1);

return [
	'lastFullAnalysisTime' => 1771004751,
	'meta' => array (
  'cacheVersion' => 'v12-linesToIgnore',
  'phpstanVersion' => '2.1.39',
  'fnsr' => false,
  'metaExtensions' => 
  array (
  ),
  'phpVersion' => 80416,
  'projectConfig' => '{conditionalTags: {Larastan\\Larastan\\Rules\\NoEnvCallsOutsideOfConfigRule: {phpstan.rules.rule: %noEnvCallsOutsideOfConfig%}, Larastan\\Larastan\\Rules\\NoModelMakeRule: {phpstan.rules.rule: %noModelMake%}, Larastan\\Larastan\\Rules\\NoUnnecessaryCollectionCallRule: {phpstan.rules.rule: %noUnnecessaryCollectionCall%}, Larastan\\Larastan\\Rules\\NoUnnecessaryEnumerableToArrayCallsRule: {phpstan.rules.rule: %noUnnecessaryEnumerableToArrayCalls%}, Larastan\\Larastan\\Rules\\OctaneCompatibilityRule: {phpstan.rules.rule: %checkOctaneCompatibility%}, Larastan\\Larastan\\Rules\\UnusedViewsRule: {phpstan.rules.rule: %checkUnusedViews%}, Larastan\\Larastan\\Rules\\NoMissingTranslationsRule: {phpstan.rules.rule: %checkMissingTranslations%}, Larastan\\Larastan\\Rules\\ModelAppendsRule: {phpstan.rules.rule: %checkModelAppends%}, Larastan\\Larastan\\Rules\\NoPublicModelScopeAndAccessorRule: {phpstan.rules.rule: %checkModelMethodVisibility%}, Larastan\\Larastan\\Rules\\NoAuthFacadeInRequestScopeRule: {phpstan.rules.rule: %checkAuthCallsWhenInRequestScope%}, Larastan\\Larastan\\Rules\\NoAuthHelperInRequestScopeRule: {phpstan.rules.rule: %checkAuthCallsWhenInRequestScope%}, Larastan\\Larastan\\ReturnTypes\\Helpers\\EnvFunctionDynamicFunctionReturnTypeExtension: {phpstan.broker.dynamicFunctionReturnTypeExtension: %generalizeEnvReturnType%}, Larastan\\Larastan\\ReturnTypes\\Helpers\\ConfigFunctionDynamicFunctionReturnTypeExtension: {phpstan.broker.dynamicFunctionReturnTypeExtension: %checkConfigTypes%}, Larastan\\Larastan\\ReturnTypes\\ConfigRepositoryDynamicMethodReturnTypeExtension: {phpstan.broker.dynamicMethodReturnTypeExtension: %checkConfigTypes%}, Larastan\\Larastan\\ReturnTypes\\ConfigFacadeCollectionDynamicStaticMethodReturnTypeExtension: {phpstan.broker.dynamicStaticMethodReturnTypeExtension: %checkConfigTypes%}, Larastan\\Larastan\\Rules\\ConfigCollectionRule: {phpstan.rules.rule: %checkConfigTypes%}}, parameters: {universalObjectCratesClasses: [Illuminate\\Http\\Request, Illuminate\\Support\\Optional], earlyTerminatingFunctionCalls: [abort, dd], mixinExcludeClasses: [Eloquent], bootstrapFiles: [bootstrap.php], checkOctaneCompatibility: false, noEnvCallsOutsideOfConfig: true, noModelMake: true, noUnnecessaryCollectionCall: true, noUnnecessaryCollectionCallOnly: [], noUnnecessaryCollectionCallExcept: [], noUnnecessaryEnumerableToArrayCalls: false, squashedMigrationsPath: [], databaseMigrationsPath: [], disableMigrationScan: false, disableSchemaScan: false, configDirectories: [], viewDirectories: [], translationDirectories: [], checkModelProperties: false, checkUnusedViews: false, checkMissingTranslations: false, checkModelAppends: true, checkModelMethodVisibility: false, generalizeEnvReturnType: false, checkConfigTypes: false, checkAuthCallsWhenInRequestScope: false, parseModelCastsMethod: false, enableMigrationCache: false, paths: [/Users/sood/dev/heatware/laravel-react-starter/app, /Users/sood/dev/heatware/laravel-react-starter/routes, /Users/sood/dev/heatware/laravel-react-starter/config], level: 5, scanDirectories: [tests], tmpDir: /Users/sood/dev/heatware/laravel-react-starter/storage/phpstan}, rules: [Larastan\\Larastan\\Rules\\UselessConstructs\\NoUselessWithFunctionCallsRule, Larastan\\Larastan\\Rules\\UselessConstructs\\NoUselessValueFunctionCallsRule, Larastan\\Larastan\\Rules\\DeferrableServiceProviderMissingProvidesRule, Larastan\\Larastan\\Rules\\ConsoleCommand\\UndefinedArgumentOrOptionRule], services: {{class: Larastan\\Larastan\\Methods\\RelationForwardsCallsExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\ModelForwardsCallsExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\EloquentBuilderForwardsCallsExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\HigherOrderTapProxyExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\HigherOrderCollectionProxyExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\StorageMethodsClassReflectionExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\Extension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\ModelFactoryMethodsClassReflectionExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\RedirectResponseMethodsClassReflectionExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\MacroMethodsClassReflectionExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\ViewWithMethodsClassReflectionExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Properties\\ModelAccessorExtension, tags: [phpstan.broker.propertiesClassReflectionExtension]}, {class: Larastan\\Larastan\\Properties\\ModelPropertyExtension, tags: [phpstan.broker.propertiesClassReflectionExtension]}, {class: Larastan\\Larastan\\Properties\\HigherOrderCollectionProxyPropertyExtension, tags: [phpstan.broker.propertiesClassReflectionExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\HigherOrderTapProxyExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ContainerArrayAccessDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension], arguments: {className: Illuminate\\Contracts\\Container\\Container}}, {class: Larastan\\Larastan\\ReturnTypes\\ContainerArrayAccessDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension], arguments: {className: Illuminate\\Container\\Container}}, {class: Larastan\\Larastan\\ReturnTypes\\ContainerArrayAccessDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension], arguments: {className: Illuminate\\Foundation\\Application}}, {class: Larastan\\Larastan\\ReturnTypes\\ContainerArrayAccessDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension], arguments: {className: Illuminate\\Contracts\\Foundation\\Application}}, {class: Larastan\\Larastan\\Properties\\ModelRelationsExtension, tags: [phpstan.broker.propertiesClassReflectionExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ModelOnlyDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ModelFactoryDynamicStaticMethodReturnTypeExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ModelDynamicStaticMethodReturnTypeExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\AppMakeDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\AuthExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\GuardDynamicStaticMethodReturnTypeExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\AuthManagerExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\DateExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\GuardExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\RequestFileExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\RequestRouteExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\RequestUserExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\EloquentBuilderExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\RelationCollectionExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\TestCaseExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\Support\\CollectionHelper}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\AuthExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\CollectExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\NowAndTodayExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\ResponseExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\ValidatorExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\LiteralExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\CollectionFilterRejectDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\CollectionWhereNotNullDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\NewModelQueryDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\FactoryDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\Types\\AbortIfFunctionTypeSpecifyingExtension, tags: [phpstan.typeSpecifier.functionTypeSpecifyingExtension], arguments: {methodName: abort, negate: false}}, {class: Larastan\\Larastan\\Types\\AbortIfFunctionTypeSpecifyingExtension, tags: [phpstan.typeSpecifier.functionTypeSpecifyingExtension], arguments: {methodName: abort, negate: true}}, {class: Larastan\\Larastan\\Types\\AbortIfFunctionTypeSpecifyingExtension, tags: [phpstan.typeSpecifier.functionTypeSpecifyingExtension], arguments: {methodName: throw, negate: false}}, {class: Larastan\\Larastan\\Types\\AbortIfFunctionTypeSpecifyingExtension, tags: [phpstan.typeSpecifier.functionTypeSpecifyingExtension], arguments: {methodName: throw, negate: true}}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\AppExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\ValueExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\StrExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\TapExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\StorageDynamicStaticMethodReturnTypeExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\Types\\GenericEloquentCollectionTypeNodeResolverExtension, tags: [phpstan.phpDoc.typeNodeResolverExtension]}, {class: Larastan\\Larastan\\Types\\ViewStringTypeNodeResolverExtension, tags: [phpstan.phpDoc.typeNodeResolverExtension]}, {class: Larastan\\Larastan\\Rules\\OctaneCompatibilityRule}, {class: Larastan\\Larastan\\Rules\\NoEnvCallsOutsideOfConfigRule, arguments: {configDirectories: %configDirectories%}}, {class: Larastan\\Larastan\\Rules\\NoModelMakeRule}, {class: Larastan\\Larastan\\Rules\\NoUnnecessaryCollectionCallRule, arguments: {onlyMethods: %noUnnecessaryCollectionCallOnly%, excludeMethods: %noUnnecessaryCollectionCallExcept%}}, {class: Larastan\\Larastan\\Rules\\NoUnnecessaryEnumerableToArrayCallsRule}, {class: Larastan\\Larastan\\Rules\\ModelAppendsRule}, {class: Larastan\\Larastan\\Rules\\NoPublicModelScopeAndAccessorRule}, {class: Larastan\\Larastan\\Types\\GenericEloquentBuilderTypeNodeResolverExtension, tags: [phpstan.phpDoc.typeNodeResolverExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\AppEnvironmentReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension], arguments: {class: Illuminate\\Foundation\\Application}}, {class: Larastan\\Larastan\\ReturnTypes\\AppEnvironmentReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension], arguments: {class: Illuminate\\Contracts\\Foundation\\Application}}, {class: Larastan\\Larastan\\ReturnTypes\\AppFacadeEnvironmentReturnTypeExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\Types\\ModelProperty\\ModelPropertyTypeNodeResolverExtension, tags: [phpstan.phpDoc.typeNodeResolverExtension], arguments: {active: %checkModelProperties%}}, {class: Larastan\\Larastan\\Types\\CollectionOf\\CollectionOfTypeNodeResolverExtension, tags: [phpstan.phpDoc.typeNodeResolverExtension]}, {class: Larastan\\Larastan\\Properties\\MigrationHelper, arguments: {databaseMigrationPath: %databaseMigrationsPath%, disableMigrationScan: %disableMigrationScan%, parser: @migrationsParser, reflectionProvider: @reflectionProvider}}, iamcalSqlParser: {class: Larastan\\Larastan\\SQL\\IamcalSqlParser, autowired: false}, sqlParserFactory: {class: Larastan\\Larastan\\SQL\\SqlParserFactory, arguments: {iamcalSqlParser: @iamcalSqlParser}}, sqlParser: {type: Larastan\\Larastan\\SQL\\SqlParser, factory: [@sqlParserFactory, create]}, {class: Larastan\\Larastan\\Properties\\SquashedMigrationHelper, arguments: {schemaPaths: %squashedMigrationsPath%, disableSchemaScan: %disableSchemaScan%}}, {class: Larastan\\Larastan\\Properties\\ModelCastHelper, arguments: {parser: @currentPhpVersionSimpleDirectParser, parseModelCastsMethod: %parseModelCastsMethod%}}, {class: Larastan\\Larastan\\Properties\\MigrationCache, arguments: {cacheDirectory: %tmpDir%, enabled: %enableMigrationCache%}}, {class: Larastan\\Larastan\\Properties\\ModelPropertyHelper}, {class: Larastan\\Larastan\\Rules\\ModelRuleHelper}, {class: Larastan\\Larastan\\Methods\\BuilderHelper, arguments: {checkProperties: %checkModelProperties%}}, {class: Larastan\\Larastan\\Rules\\RelationExistenceRule, tags: [phpstan.rules.rule]}, {class: Larastan\\Larastan\\Rules\\CheckDispatchArgumentTypesCompatibleWithClassConstructorRule, arguments: {dispatchableClass: Illuminate\\Foundation\\Bus\\Dispatchable}, tags: [phpstan.rules.rule]}, {class: Larastan\\Larastan\\Rules\\CheckDispatchArgumentTypesCompatibleWithClassConstructorRule, arguments: {dispatchableClass: Illuminate\\Foundation\\Events\\Dispatchable}, tags: [phpstan.rules.rule]}, {class: Larastan\\Larastan\\Properties\\Schema\\MySqlDataTypeToPhpTypeConverter}, {class: Larastan\\Larastan\\LarastanStubFilesExtension, tags: [phpstan.stubFilesExtension]}, {class: Larastan\\Larastan\\Rules\\UnusedViewsRule}, {class: Larastan\\Larastan\\Collectors\\UsedViewFunctionCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedEmailViewCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedViewMakeCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedViewFacadeMakeCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedRouteFacadeViewCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedViewInAnotherViewCollector}, {class: Larastan\\Larastan\\Support\\ViewFileHelper, arguments: {viewDirectories: %viewDirectories%}}, {class: Larastan\\Larastan\\Support\\ViewParser, arguments: {parser: @currentPhpVersionSimpleDirectParser}}, {class: Larastan\\Larastan\\Rules\\NoMissingTranslationsRule, arguments: {translationDirectories: %translationDirectories%}}, {class: Larastan\\Larastan\\Collectors\\UsedTranslationFunctionCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedTranslationTranslatorCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedTranslationFacadeCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedTranslationViewCollector}, {class: Larastan\\Larastan\\ReturnTypes\\ApplicationMakeDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ContainerMakeDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ConsoleCommand\\ArgumentDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ConsoleCommand\\HasArgumentDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ConsoleCommand\\OptionDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ConsoleCommand\\HasOptionDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\TranslatorGetReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\LangGetReturnTypeExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\TransHelperReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\DoubleUnderscoreHelperReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\AppMakeHelper}, {class: Larastan\\Larastan\\Internal\\ConsoleApplicationResolver}, {class: Larastan\\Larastan\\Internal\\ConsoleApplicationHelper}, {class: Larastan\\Larastan\\Support\\HigherOrderCollectionProxyHelper}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\ConfigFunctionDynamicFunctionReturnTypeExtension}, {class: Larastan\\Larastan\\ReturnTypes\\ConfigRepositoryDynamicMethodReturnTypeExtension}, {class: Larastan\\Larastan\\ReturnTypes\\ConfigFacadeCollectionDynamicStaticMethodReturnTypeExtension}, {class: Larastan\\Larastan\\Support\\ConfigParser, arguments: {parser: @currentPhpVersionSimpleDirectParser, configPaths: %configDirectories%}}, {class: Larastan\\Larastan\\Internal\\ConfigHelper}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\EnvFunctionDynamicFunctionReturnTypeExtension}, {class: Larastan\\Larastan\\ReturnTypes\\FormRequestSafeDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\Rules\\NoAuthFacadeInRequestScopeRule}, {class: Larastan\\Larastan\\Rules\\NoAuthHelperInRequestScopeRule}, {class: Larastan\\Larastan\\Rules\\ConfigCollectionRule}, {class: Illuminate\\Filesystem\\Filesystem, autowired: self}, migrationsParser: {class: PHPStan\\Parser\\CachedParser, arguments: {originalParser: @currentPhpVersionSimpleDirectParser, cachedNodesByStringCountMax: %cache.nodesByStringCountMax%}, autowired: false}}}',
  'analysedPaths' => 
  array (
    0 => '/Users/sood/dev/heatware/laravel-react-starter/app',
    1 => '/Users/sood/dev/heatware/laravel-react-starter/routes',
    2 => '/Users/sood/dev/heatware/laravel-react-starter/config',
  ),
  'scannedFiles' => 
  array (
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Contracts/FeatureFlagContractTest.php' => 'c01b67be0b5536831e11c505bd0aa872dc23ec4cc02f605e0ea043282445e98e',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/CreatesApplication.php' => 'ff49d2d2197546e6730769fd4f11e1acf7169b83cdeac102af5d77c07f748898',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminAuditLogTest.php' => 'e90eefc549fe98568151266782006e8ce2794ac64214e39f53b52982aec16c07',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminBillingDashboardTest.php' => 'a9eb230eecad30a350943bac09c3a27c2bba7cc4e8c0f8e49209cf3380ffaeb6',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminBillingShowTest.php' => '50ff48fcae03472831a054b5c1082901c6f19e07848c1a3219ff7a624ad7fe55',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminBillingSubscriptionsTest.php' => 'ef9ff2671c66416cf77dea8d14aa572cfa89a9971511ec015e1f33b4427f65ad',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminBulkActionsTest.php' => 'f30e0dcdc4fd50aa5b8c8fa1dcbe75725729fbd11a09482e3546f7eefd824326',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminConfigTest.php' => 'b6ddd4471b5243f624f0275d8b1f3ffb612b1a294168848474df811599b993a4',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminDashboardTest.php' => 'c4479fc890ebf02b3cae2e276492e3f26398eb4b160500869e1aa9f03f337a54',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminEdgeCaseTest.php' => 'f438322d3cbcdfbe026c66f4ba5b2180d252daa7b82298d2bf0e6a93161bd81f',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminFeatureFlagTest.php' => 'efcdcbf04ba1e36312c984f3ddc86eb2a4efeba63632800cec7358596d50c389',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminHealthTest.php' => '82d79dd17dcd4773edb42cf5cc5e50259e783ae7cf81db018216a47a456f6398',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminImpersonationTest.php' => '12a36a9f830c4b698d14a75446701d7ea12a6553b1fe37840aeb46c14c465824',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminMiddlewareTest.php' => 'ed110f4d6f14ca2e33b899dc9a81c28de95c69e2e7bc2ec7fbe3854e76514d04',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminNotificationsTest.php' => 'a680682991e06cd8306bfe76d6431cec19b94bdf34fca34d6108ea4976373cde',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminSocialAuthTest.php' => '1d9a15e90817ab45e5df1bf3f131401bfa3c90542710075c31843684e580ab22',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminSystemTest.php' => '2b2e97f68891f13a418d5e20981b92044ad5a9bcc829af55d770ef1bc8c52794',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminTokensTest.php' => '757b08e10d06e77eeb479c1932d0c95a8d0e9fdffa98cde16c3a1ebde808191c',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminTwoFactorTest.php' => 'f003ecc7817d895153d8e5e49f25f7ce34e179379c64195238ed1d47de8aca5c',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminUsersTest.php' => 'b61145f359e8e9e5deef3faf6e5dccc1d15435c9f9c0f4cfed4e4d0e2fe05487',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Admin/AdminWebhooksTest.php' => 'f522fbd76c0dff9958ae0cea8d52ca49c71d526b39e3f8a07fdae8e7f420c02a',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Api/ApiTokenControllerTest.php' => 'fc29151bebcaeb8fdaec07477e98d52873abf5aac7ffb872bf4d28288e2d9b10',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Api/NotificationControllerTest.php' => 'b06394a7df3e9ac85c2e9e50705b334fcaaeb91b90a5b41133e56a5777940f0b',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Api/NotificationFeatureDisabledTest.php' => 'fbb2d1f8dd84d4014421413cc5fea7f2acf736e06b88602717de0c76b87d09f7',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Api/RateLimitHeadersTest.php' => '57dc2d3273698a2e89231c35cfa86debcd6d4b222c7366692947477846f7fe7c',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Api/TokenDeletionTest.php' => '10038f3ecc56b3a70259b1295087d3bfad9829dbd19bf50c9cef8d6640341b7c',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Api/TokenExpirationTest.php' => 'b0f54ec2b92aed16afb9bf1c43b3f2759e08680717aa8f923e13b770f7af7ec8',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Api/UpdateSettingRequestValidationTest.php' => 'a15d74361b51512ca9d04430d9bd6ae39bffbf76195195bbe9f3c038fed2735f',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Api/UserSettingsControllerTest.php' => '9160f7a2d616f8816db7cad797531ab1a4e83c6e26883ad3677f527bddbfd8a6',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/ApiDocsTest.php' => '9b943d4af8e66daf74faca33c8416121a34d29abf096a84c8cc4f61a14e1a226',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/AuditLogTest.php' => '6fabf36aa17713b0ca4f14f4c5e7c8a91fb82227a3db30b532895b6a12a954ec',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Auth/AuthenticationTest.php' => '08685bdecdf5c6c8050faa59ebbe8ec6ef4204abea7d7493572f541ead16b601',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Auth/ConfirmPasswordTest.php' => '2069595e818e1b70a56c979461e20690c41866def7a3c8382dee0844f4076cf1',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Auth/EmailVerificationNotificationTest.php' => 'b968a07e47333764f5b3a37ebec038afa0b8d1b5d28cade23142999fc6205f16',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Auth/EmailVerificationTest.php' => 'f4c3a9d6949d5186205fbc4b283f3d439f63dcd02c881fa18838bc4e41c81bd5',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Auth/LoginRateLimitTest.php' => '8d67266d7dc48c3ad9f4b07f148428e2ea6189c786daed4a12c8485b672edfd3',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Auth/LoginSecurityTest.php' => '3c148df501c67415d561393b65d4f9234349b8425c450bed975937cf8eaa94a9',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Auth/LogoutSecurityTest.php' => '16d51fd3b62829378e292cf2ebde824c3cb29ec78c253d13346acfff2065a3c1',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Auth/PasswordResetEdgeCaseTest.php' => 'f5373572faf707907cb3483d8f8867d92d187f40743d1396ddad50e21e5b7946',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Auth/PasswordResetTest.php' => '70cd7cfb5ebf0588f5a9c4a710f9dec61d381581b14667acf369e9ea0ac8ad40',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Auth/PasswordUpdateTest.php' => 'a2865811f0209ae5888e405edbfd3b5f0e64aa3ce5240332e5609902e44ad2c7',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Auth/RateLimitingTest.php' => 'da5d1dd87f148d2f21f036be7c04844d5479d26f9ccf3a8153052545f73d486f',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Auth/RegistrationSecurityTest.php' => '92845bd6155ae37ab056a635fd864f8f2b9d771271d7af1c83a84d2d2634a245',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Auth/RegistrationTest.php' => '605a804748f9a3c9f97bb46347b086ca68262c3e9416e8cdda5422d3ead35402',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Auth/RememberMeTest.php' => '813a77d2d000fbefabbd35136ac453dbd4acadd00317ed89f007b8fc491bff07',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Auth/SocialAuthTest.php' => '670f20371f1af19fd7d563111ab3383f9b5a47171e47621462fa272eb7bd9a7f',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/AccountDeletionBillingTest.php' => 'd0f1b3ea3015ba9ba866e86ec2af1c6d9f30d0dc6bc854972cd974f19d7c805b',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/BillingFeatureFlagTest.php' => '8cd126d7cbc7bddac244f83621e2fef2503abfb1fe95bb5a1df543ff2cf507be',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/BillingPageTest.php' => '8f57b2c99228b06932121bf7d7a128ece151c2c0a140a23ce7b40cf1ae043629',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/BillingPortalTest.php' => '77afb032b82125ea8c491d965d841578a68bdcd3d99a728d059fa74e6fbd6446',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/CacheInvalidationTest.php' => '8a4b251e0c8ae72507fdb8f2475c2944c1fa07ceb903fdf1843bfde4491b06c7',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/ConcurrencyProtectionTest.php' => '1d6d3a7017b5c9d0d542347844c5eda78235af02ccd8433f4e8c4741681fee81',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/IncompletePaymentReminderTest.php' => 'b2e50494fffe6408dcb0992fe72e624686b487f1625e248e1e5c6c37af72b45c',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/PastDueGracePeriodTest.php' => '25e332300fa8fbe464f6f766a46b5d067a400be48b8519246fe0f3852904c571',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/PaymentMethodTest.php' => '14ecb588991a2ad59e06d2676d2df8138d98a658184a566875845cd0c9b4c12e',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/PaymentNotificationTest.php' => '0371a9e8bae8996fc9ba0ea4c3bc13313f8738f52f6b897edfd001c0a82b72de',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/PricingPageTest.php' => '4b56466053986575819c256f35dfb042e2e1e25ad8230ae5f33bc3abbde1b543',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/RefundNotificationTest.php' => 'd18200f12c98ce08ccf1ad23178518fdc0c7e00af7a3d4c4361f7455d4cd918c',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/SeatManagementTest.php' => '97faff0751435588a1f8e570d409c129cd981975233a06a12df612e7ece8a6c9',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/StripeWebhookTest.php' => 'ccf99d1b8e99b2b77b8d411bc737742ddeb68333a682fa4de6c39578a15d0ca4',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/SubscriptionCreationTest.php' => '67fe2557352c66b44d123cfe212d80bbaf650bb12b661e66ca20e173f947fc95',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/SubscriptionPlanSwapTest.php' => '8dbd15c688682774cf0799e5724bc68e12f76c7765c262be27ddbb75c9e0d806',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/SubscriptionStateChangeTest.php' => '7148ba7b0ca03621531b2c0f8c43cf6ab46f691bbe1dd2a9217c85c55f56cf9e',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/TierResolutionTest.php' => '35ceaf7fd748941a3807ac635cdbd4825964c3535d37138158f59ccb7367bf73',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Billing/UniqueSubscriptionConstraintTest.php' => '7371fb9c71c4433efeeb7be7387371d7b6e1da464b734106f5c7e68a4574fd9b',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/ChartsControllerTest.php' => 'd999cc5f6f6450b77467232fd706a8297aa61a5efb42e913958cc74415360664',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/DashboardControllerTest.php' => '0f5cd3d65a65835480fdf0c08954bbe518ab8538353be4a04b1265b4d3db6644',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Database/SubscriptionItemsIndexTest.php' => '5ece048b908ed3f51085d764d4a9496a762a029373c9680243e23518781caef8',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/ErrorPageTest.php' => 'cd7664482c12525df1e25a0e7ac862be6abc192d49b6c1a3bb502f55ec86984d',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/ExampleTest.php' => '27f8ad6ccb9af5d464cab917925722621f7418352b862c8577c55e90085b5c67',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/ExceptionHandlerTest.php' => 'f249b7ac35b8f8b487d2e9c4a46f3f67724b146cfa48fcb269d4626f743b3018',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/ExportTest.php' => 'f8c94c13a0edd732d23920ea7d660bf442b0e51479ce9027b1b27001352d4a6c',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/HealthCheckTest.php' => '4e532324d905e0b7f51b65436a4ad017627eeaf10193a51587e8bcd20f7bd012',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Middleware/EnsureOnboardingCompletedTest.php' => 'f371ce9fa49d72ea3e4116a6677e91a29d9e23af5af74fcecde26e89296c3a47',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Middleware/RequestIdMiddlewareTest.php' => '734f6730ebb1c1502b1af3766266cb2a529411159efe47a7d636282b172eb97d',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Middleware/SecurityHeadersTest.php' => '828a1bd551aa85e7e2afa3b67761802ab701bbff45402ac2c8ae431d23425b0e',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/OnboardingTest.php' => '0a0a6db3f9360b58ef0ea57cef0fb4b052a6c47797e8e9f74cf95f5e6d87e20b',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/ProfileTest.php' => 'c7cb3898fdec2fa93015bd3c2f2e067680d2b107edd07ee704519621ea567690',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/PruneAuditLogsTest.php' => '774566de6deceb05967583831d09e386eaa0872755739d42ebf52f08ee447506',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/SeoTest.php' => '61dd4b9615e5f490aa6a07a89bf504be2f6bb2769c488279c6085e57a95ccbfe',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Settings/ApiTokenPageTest.php' => 'af34df5e190fb3f510aa07dce90060989a11459e649306bcfbece05751824b9f',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/SoftDeleteTest.php' => 'bd2a92475b701cffde99929fbdec36fadd8079195b3e41a940494647f381dadc',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/TrustedProxiesTest.php' => '7b21454d9418a357a75d8b2e9ccc6a617f214d5b0ac1c15ad9f373ba16575b16',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/TwoFactor/TwoFactorChallengeTest.php' => 'a6bf3dc9cd678252e7039bda07cf351d248b8a45dad673853f6ff9b78787e623',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/TwoFactor/TwoFactorRecoveryTest.php' => '59a105491312702d6f8e07553fd21b308426ca236c791df8e82776c16ea618e5',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/TwoFactor/TwoFactorSetupTest.php' => 'c1c86b7e62d68fd59f2e10bf82f77d9db65cd35362a1765f0d0a32ac88326684',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Webhook/IncomingWebhookTest.php' => '2f65f1ac0672503ac6311cf5857e1a34204a42024da985e7e4257a13e14d92ff',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Webhook/VerifyWebhookSignatureTest.php' => 'bbcefba3cbc1a3f28cce94f1068e2d02019d4912ccacb9515527fd3b79b45537',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Webhook/WebhookEndpointApiTest.php' => '9196d5548cadbd4178f1e59aff1effe0abe2a0941456b63f10efb625b26ba931',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Feature/Webhook/WebhookPageTest.php' => '7bf07fa7d9045362dab862dfb7926f76579412f8cc7872d08c8f4d2766e9482b',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Pest.php' => '1b7d4a98c1f502189c161cd285bd0bda6ab2a1cdfd8c85a349cc0ab65530003d',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/TestCase.php' => '63ea63571e11d24c98bec13f0e09d4951e039288fe255067eebfb42c214718c5',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Middleware/EnsureSubscribedTest.php' => 'f597c9c0986b1efb0141b7be91836966e84515fda9b2b2a269707d5b95e7f789',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Middleware/HandleInertiaRequestsTest.php' => '71c803fcf1be69be8471bbfee6e994f96e3d09f5890ff43bbcd8abcc0b592aec',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Models/SocialAccountTest.php' => '8beaf78290b8b8dbf9d489ef3d4ce02475f2897f087ca1db8ead376b9c474f0b',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Models/UserSettingTest.php' => '9cb7e24e707937d0bdc7dd839ea2dad34d18ef1e247b98abdabb5b83339824c8',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Models/UserTest.php' => 'ad32100f439619bccbe2c53184b5ab4069c7c2def15a40a60ab5f99c5b594072',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Models/UserTwoFactorTest.php' => '7a568fc76091757e8679e4e6f7df7fde3745a89f6c761c8b454c65c9f290f179',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Policies/UserPolicyTest.php' => '8bc39b05a14c2af9f31650afd8709f0a32edbb7ee0ea7a1d70dc6cf586abe032',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Requests/Auth/LoginRequestTest.php' => '7dbf37afdad96a98df9f86a635531667874596b21161874f8bfc608e43b54e48',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Requests/Auth/RegisterRequestTest.php' => 'f6d1720ac566c946b22a4df409bf79d3e5c3ae4aaa0650cc3c12e09707e7dba9',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Requests/ProfileUpdateRequestTest.php' => '5bb294263d086b90a0b3e50d8ee6dd2143aea497ff02a8c28fdedf8e30b156e2',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Services/AdminBillingStatsServiceTest.php' => 'ea2927eb562e6505383554d0396aa23fad6a39cd04db3bf000bdba0eaacee0fc',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Services/AuditServiceTest.php' => 'c4f13cb22ddea32c5a9d75cf4f4763570139ea721b0323d89966d9f2b04c31f9',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Services/BillingServiceIntegrationTest.php' => '83016645c253a54145cfd9a198a82d907df743d952fa5430dddede9b68d991d3',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Services/BillingServiceTest.php' => 'd9f584cad750886499c24a3bc47ba0fedc5554cf505755a82f2016bcfe4307ba',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Services/FeatureFlagServiceTest.php' => 'fd7709dd5318658782888d520ac7c3300bf9814ff646dded4370cb6cd1258aef',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Services/HealthCheckServiceTest.php' => '74ec174548fd85390bd44df38c3dcf28e6bacaf93a588cc60b42320c2fd99fff',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Services/IncomingWebhookServiceTest.php' => 'fdb39b9bb1496bf0da47b266eb5922ebd575015c668665d1918ba47e2e73124a',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Services/PlanLimitServiceTest.php' => '6ff5c9d3f763d04dd7ae59457bc4e131aaeac8642e138191a7754812cafe2e79',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Services/SessionDataMigrationServiceTest.php' => '0e768921be8f8869f7e61f0281eade2741e070559f07a21a9889313efb13f3c8',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Services/SocialAuthServiceTest.php' => '21fc670577cfd90504432923a3cf75d7fbbebdc75bbe246edb5f1ccaa3ed5fa8',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Services/WebhookServiceTest.php' => '5164fd815dd3e1e2e649248c3df806eb9a6c9d75e0561e1ae4ead8243e901771',
    '/Users/sood/dev/heatware/laravel-react-starter/tests/Unit/Support/CsvExportTest.php' => '076f10b2039b9f2ba0f0f31344e60231e868b14837677e14e902469ab126c7c3',
  ),
  'composerLocks' => 
  array (
    '/Users/sood/dev/heatware/laravel-react-starter/composer.lock' => '6b49d63314a6b03a40ee3198742fd73d462501349d4d6697d098df2cd5ed4ff6',
  ),
  'composerInstalled' => 
  array (
    '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/installed.php' => 
    array (
      'versions' => 
      array (
        'bacon/bacon-qr-code' => 
        array (
          'pretty_version' => 'v3.0.3',
          'version' => '3.0.3.0',
          'reference' => '36a1cb2b81493fa5b82e50bf8068bf84d1542563',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../bacon/bacon-qr-code',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'brianium/paratest' => 
        array (
          'pretty_version' => 'v7.17.0',
          'version' => '7.17.0.0',
          'reference' => '53cb90a6aa3ef3840458781600628ade058a18b9',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../brianium/paratest',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'brick/math' => 
        array (
          'pretty_version' => '0.14.8',
          'version' => '0.14.8.0',
          'reference' => '63422359a44b7f06cae63c3b429b59e8efcc0629',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../brick/math',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'carbonphp/carbon-doctrine-types' => 
        array (
          'pretty_version' => '3.2.0',
          'version' => '3.2.0.0',
          'reference' => '18ba5ddfec8976260ead6e866180bd5d2f71aa1d',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../carbonphp/carbon-doctrine-types',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'colinodell/json5' => 
        array (
          'pretty_version' => 'v3.0.0',
          'version' => '3.0.0.0',
          'reference' => '5724d21bc5c910c2560af1b8915f0cc0163579c8',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../colinodell/json5',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'composer/pcre' => 
        array (
          'pretty_version' => '3.3.2',
          'version' => '3.3.2.0',
          'reference' => 'b2bed4734f0cc156ee1fe9c0da2550420d99a21e',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/./pcre',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'composer/xdebug-handler' => 
        array (
          'pretty_version' => '3.0.5',
          'version' => '3.0.5.0',
          'reference' => '6c1925561632e83d60a44492e0b344cf48ab85ef',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/./xdebug-handler',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'cordoval/hamcrest-php' => 
        array (
          'dev_requirement' => true,
          'replaced' => 
          array (
            0 => '*',
          ),
        ),
        'dasprid/enum' => 
        array (
          'pretty_version' => '1.0.7',
          'version' => '1.0.7.0',
          'reference' => 'b5874fa9ed0043116c72162ec7f4fb50e02e7cce',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../dasprid/enum',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'davedevelopment/hamcrest-php' => 
        array (
          'dev_requirement' => true,
          'replaced' => 
          array (
            0 => '*',
          ),
        ),
        'dflydev/dot-access-data' => 
        array (
          'pretty_version' => 'v3.0.3',
          'version' => '3.0.3.0',
          'reference' => 'a23a2bf4f31d3518f3ecb38660c95715dfead60f',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../dflydev/dot-access-data',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'doctrine/deprecations' => 
        array (
          'pretty_version' => '1.1.6',
          'version' => '1.1.6.0',
          'reference' => 'd4fe3e6fd9bb9e72557a19674f44d8ac7db4c6ca',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../doctrine/deprecations',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'doctrine/inflector' => 
        array (
          'pretty_version' => '2.1.0',
          'version' => '2.1.0.0',
          'reference' => '6d6c96277ea252fc1304627204c3d5e6e15faa3b',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../doctrine/inflector',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'doctrine/lexer' => 
        array (
          'pretty_version' => '3.0.1',
          'version' => '3.0.1.0',
          'reference' => '31ad66abc0fc9e1a1f2d9bc6a42668d2fbbcd6dd',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../doctrine/lexer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'dragonmantank/cron-expression' => 
        array (
          'pretty_version' => 'v3.6.0',
          'version' => '3.6.0.0',
          'reference' => 'd61a8a9604ec1f8c3d150d09db6ce98b32675013',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../dragonmantank/cron-expression',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'egulias/email-validator' => 
        array (
          'pretty_version' => '4.0.4',
          'version' => '4.0.4.0',
          'reference' => 'd42c8731f0624ad6bdc8d3e5e9a4524f68801cfa',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../egulias/email-validator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'fakerphp/faker' => 
        array (
          'pretty_version' => 'v1.24.1',
          'version' => '1.24.1.0',
          'reference' => 'e0ee18eb1e6dc3cda3ce9fd97e5a0689a88a64b5',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../fakerphp/faker',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'fidry/cpu-core-counter' => 
        array (
          'pretty_version' => '1.3.0',
          'version' => '1.3.0.0',
          'reference' => 'db9508f7b1474469d9d3c53b86f817e344732678',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../fidry/cpu-core-counter',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'filp/whoops' => 
        array (
          'pretty_version' => '2.18.4',
          'version' => '2.18.4.0',
          'reference' => 'd2102955e48b9fd9ab24280a7ad12ed552752c4d',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../filp/whoops',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'fruitcake/php-cors' => 
        array (
          'pretty_version' => 'v1.4.0',
          'version' => '1.4.0.0',
          'reference' => '38aaa6c3fd4c157ffe2a4d10aa8b9b16ba8de379',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../fruitcake/php-cors',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'graham-campbell/result-type' => 
        array (
          'pretty_version' => 'v1.1.4',
          'version' => '1.1.4.0',
          'reference' => 'e01f4a821471308ba86aa202fed6698b6b695e3b',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../graham-campbell/result-type',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'guzzlehttp/guzzle' => 
        array (
          'pretty_version' => '7.10.0',
          'version' => '7.10.0.0',
          'reference' => 'b51ac707cfa420b7bfd4e4d5e510ba8008e822b4',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../guzzlehttp/guzzle',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'guzzlehttp/promises' => 
        array (
          'pretty_version' => '2.3.0',
          'version' => '2.3.0.0',
          'reference' => '481557b130ef3790cf82b713667b43030dc9c957',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../guzzlehttp/promises',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'guzzlehttp/psr7' => 
        array (
          'pretty_version' => '2.8.0',
          'version' => '2.8.0.0',
          'reference' => '21dc724a0583619cd1652f673303492272778051',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../guzzlehttp/psr7',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'guzzlehttp/uri-template' => 
        array (
          'pretty_version' => 'v1.0.5',
          'version' => '1.0.5.0',
          'reference' => '4f4bbd4e7172148801e76e3decc1e559bdee34e1',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../guzzlehttp/uri-template',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'hamcrest/hamcrest-php' => 
        array (
          'pretty_version' => 'v2.1.1',
          'version' => '2.1.1.0',
          'reference' => 'f8b1c0173b22fa6ec77a81fe63e5b01eba7e6487',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../hamcrest/hamcrest-php',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'iamcal/sql-parser' => 
        array (
          'pretty_version' => 'v0.7',
          'version' => '0.7.0.0',
          'reference' => '610392f38de49a44dab08dc1659960a29874c4b8',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../iamcal/sql-parser',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'illuminate/auth' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/broadcasting' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/bus' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/cache' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/collections' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/concurrency' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/conditionable' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/config' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/console' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/container' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/contracts' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/cookie' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/database' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/encryption' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/events' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/filesystem' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/hashing' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/http' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/json-schema' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/log' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/macroable' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/mail' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/notifications' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/pagination' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/pipeline' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/process' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/queue' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/redis' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/reflection' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/routing' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/session' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/support' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/testing' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/translation' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/validation' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'illuminate/view' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.51.0',
          ),
        ),
        'inertiajs/inertia-laravel' => 
        array (
          'pretty_version' => 'v2.0.20',
          'version' => '2.0.20.0',
          'reference' => '02a719d1120378aed68053b5b2d35157140df50e',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../inertiajs/inertia-laravel',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'infection/abstract-testframework-adapter' => 
        array (
          'pretty_version' => '0.5.0',
          'version' => '0.5.0.0',
          'reference' => '18925e20d15d1a5995bb85c9dc09e8751e1e069b',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../infection/abstract-testframework-adapter',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'infection/extension-installer' => 
        array (
          'pretty_version' => '0.1.2',
          'version' => '0.1.2.0',
          'reference' => '9b351d2910b9a23ab4815542e93d541e0ca0cdcf',
          'type' => 'composer-plugin',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../infection/extension-installer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'infection/include-interceptor' => 
        array (
          'pretty_version' => '0.2.5',
          'version' => '0.2.5.0',
          'reference' => '0cc76d95a79d9832d74e74492b0a30139904bdf7',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../infection/include-interceptor',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'infection/infection' => 
        array (
          'pretty_version' => '0.32.4',
          'version' => '0.32.4.0',
          'reference' => 'a2b0a3e47b56bd2f27ca13caecae47baa7e5abe8',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../infection/infection',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'infection/mutator' => 
        array (
          'pretty_version' => '0.4.1',
          'version' => '0.4.1.0',
          'reference' => '3c976d721b02b32f851ee4e15d553ef1e9186d1d',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../infection/mutator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'jean85/pretty-package-versions' => 
        array (
          'pretty_version' => '2.1.1',
          'version' => '2.1.1.0',
          'reference' => '4d7aa5dab42e2a76d99559706022885de0e18e1a',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../jean85/pretty-package-versions',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'justinrainbow/json-schema' => 
        array (
          'pretty_version' => 'v6.7.1',
          'version' => '6.7.1.0',
          'reference' => 'cd3137ab4ad45033230f530ab7d5618d583c17be',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../justinrainbow/json-schema',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'knuckleswtf/scribe' => 
        array (
          'pretty_version' => '5.7.0',
          'version' => '5.7.0.0',
          'reference' => '81cadf88fca6b78a943af413c3818977c264b591',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../knuckleswtf/scribe',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'kodova/hamcrest-php' => 
        array (
          'dev_requirement' => true,
          'replaced' => 
          array (
            0 => '*',
          ),
        ),
        'laragear/meta' => 
        array (
          'pretty_version' => 'v3.1.2',
          'version' => '3.1.2.0',
          'reference' => 'f204694aa232f56cfada912c56fb14e9ff643fcd',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../laragear/meta',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'laragear/meta-model' => 
        array (
          'pretty_version' => 'v2.0.0',
          'version' => '2.0.0.0',
          'reference' => '1973517263d9ff1cfaabf682f3c2883425271dbc',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../laragear/meta-model',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'laragear/two-factor' => 
        array (
          'pretty_version' => 'v3.0.0',
          'version' => '3.0.0.0',
          'reference' => 'e895ce4f8eb96fa813e687a4138e6f88ac109748',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../laragear/two-factor',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'larastan/larastan' => 
        array (
          'pretty_version' => 'v3.9.2',
          'version' => '3.9.2.0',
          'reference' => '2e9ed291bdc1969e7f270fb33c9cdf3c912daeb2',
          'type' => 'phpstan-extension',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../larastan/larastan',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'laravel/breeze' => 
        array (
          'pretty_version' => 'v2.3.8',
          'version' => '2.3.8.0',
          'reference' => '1a29c5792818bd4cddf70b5f743a227e02fbcfcd',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../laravel/breeze',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'laravel/cashier' => 
        array (
          'pretty_version' => 'v16.2.0',
          'version' => '16.2.0.0',
          'reference' => '9634b60c196ef1a512aa4f9543b6c2a1d64dff85',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../laravel/cashier',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'laravel/framework' => 
        array (
          'pretty_version' => 'v12.51.0',
          'version' => '12.51.0.0',
          'reference' => 'ce4de3feb211e47c4f959d309ccf8a2733b1bc16',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../laravel/framework',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'laravel/pail' => 
        array (
          'pretty_version' => 'v1.2.6',
          'version' => '1.2.6.0',
          'reference' => 'aa71a01c309e7f66bc2ec4fb1a59291b82eb4abf',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../laravel/pail',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'laravel/pint' => 
        array (
          'pretty_version' => 'v1.27.1',
          'version' => '1.27.1.0',
          'reference' => '54cca2de13790570c7b6f0f94f37896bee4abcb5',
          'type' => 'project',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../laravel/pint',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'laravel/prompts' => 
        array (
          'pretty_version' => 'v0.3.13',
          'version' => '0.3.13.0',
          'reference' => 'ed8c466571b37e977532fb2fd3c272c784d7050d',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../laravel/prompts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'laravel/sail' => 
        array (
          'pretty_version' => 'v1.53.0',
          'version' => '1.53.0.0',
          'reference' => 'e340eaa2bea9b99192570c48ed837155dbf24fbb',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../laravel/sail',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'laravel/sanctum' => 
        array (
          'pretty_version' => 'v4.3.1',
          'version' => '4.3.1.0',
          'reference' => 'e3b85d6e36ad00e5db2d1dcc27c81ffdf15cbf76',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../laravel/sanctum',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'laravel/serializable-closure' => 
        array (
          'pretty_version' => 'v2.0.9',
          'version' => '2.0.9.0',
          'reference' => '8f631589ab07b7b52fead814965f5a800459cb3e',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../laravel/serializable-closure',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'laravel/tinker' => 
        array (
          'pretty_version' => 'v2.11.1',
          'version' => '2.11.1.0',
          'reference' => 'c9f80cc835649b5c1842898fb043f8cc098dd741',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../laravel/tinker',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/commonmark' => 
        array (
          'pretty_version' => '2.8.0',
          'version' => '2.8.0.0',
          'reference' => '4efa10c1e56488e658d10adf7b7b7dcd19940bfb',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../league/commonmark',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/config' => 
        array (
          'pretty_version' => 'v1.2.0',
          'version' => '1.2.0.0',
          'reference' => '754b3604fb2984c71f4af4a9cbe7b57f346ec1f3',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../league/config',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/flysystem' => 
        array (
          'pretty_version' => '3.31.0',
          'version' => '3.31.0.0',
          'reference' => '1717e0b3642b0df65ecb0cc89cdd99fa840672ff',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../league/flysystem',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/flysystem-local' => 
        array (
          'pretty_version' => '3.31.0',
          'version' => '3.31.0.0',
          'reference' => '2f669db18a4c20c755c2bb7d3a7b0b2340488079',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../league/flysystem-local',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/mime-type-detection' => 
        array (
          'pretty_version' => '1.16.0',
          'version' => '1.16.0.0',
          'reference' => '2d6702ff215bf922936ccc1ad31007edc76451b9',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../league/mime-type-detection',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/uri' => 
        array (
          'pretty_version' => '7.8.0',
          'version' => '7.8.0.0',
          'reference' => '4436c6ec8d458e4244448b069cc572d088230b76',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../league/uri',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/uri-interfaces' => 
        array (
          'pretty_version' => '7.8.0',
          'version' => '7.8.0.0',
          'reference' => 'c5c5cd056110fc8afaba29fa6b72a43ced42acd4',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../league/uri-interfaces',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'marc-mabe/php-enum' => 
        array (
          'pretty_version' => 'v4.7.2',
          'version' => '4.7.2.0',
          'reference' => 'bb426fcdd65c60fb3638ef741e8782508fda7eef',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../marc-mabe/php-enum',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'mockery/mockery' => 
        array (
          'pretty_version' => '1.6.12',
          'version' => '1.6.12.0',
          'reference' => '1f4efdd7d3beafe9807b08156dfcb176d18f1699',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../mockery/mockery',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'moneyphp/money' => 
        array (
          'pretty_version' => 'v4.8.0',
          'version' => '4.8.0.0',
          'reference' => 'b358727ea5a5cd2d7475e59c31dfc352440ae7ec',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../moneyphp/money',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'monolog/monolog' => 
        array (
          'pretty_version' => '3.10.0',
          'version' => '3.10.0.0',
          'reference' => 'b321dd6749f0bf7189444158a3ce785cc16d69b0',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../monolog/monolog',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'mpociot/laravel-apidoc-generator' => 
        array (
          'dev_requirement' => true,
          'replaced' => 
          array (
            0 => '*',
          ),
        ),
        'mpociot/reflection-docblock' => 
        array (
          'pretty_version' => '1.0.1',
          'version' => '1.0.1.0',
          'reference' => 'c8b2e2b1f5cebbb06e2b5ccbf2958f2198867587',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../mpociot/reflection-docblock',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'mtdowling/cron-expression' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => '^1.0',
          ),
        ),
        'myclabs/deep-copy' => 
        array (
          'pretty_version' => '1.13.4',
          'version' => '1.13.4.0',
          'reference' => '07d290f0c47959fd5eed98c95ee5602db07e0b6a',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../myclabs/deep-copy',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'nesbot/carbon' => 
        array (
          'pretty_version' => '3.11.1',
          'version' => '3.11.1.0',
          'reference' => 'f438fcc98f92babee98381d399c65336f3a3827f',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../nesbot/carbon',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'nette/schema' => 
        array (
          'pretty_version' => 'v1.3.4',
          'version' => '1.3.4.0',
          'reference' => '086497a2f34b82fede9b5a41cc8e131d087cd8f7',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../nette/schema',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'nette/utils' => 
        array (
          'pretty_version' => 'v4.1.3',
          'version' => '4.1.3.0',
          'reference' => 'bb3ea637e3d131d72acc033cfc2746ee893349fe',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../nette/utils',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'nicmart/tree' => 
        array (
          'pretty_version' => '0.10.1',
          'version' => '0.10.1.0',
          'reference' => '2ef11e329d26005ef49dbacd0223bcfd2515b6cc',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../nicmart/tree',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'nikic/php-parser' => 
        array (
          'pretty_version' => 'v5.7.0',
          'version' => '5.7.0.0',
          'reference' => 'dca41cd15c2ac9d055ad70dbfd011130757d1f82',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../nikic/php-parser',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'nunomaduro/collision' => 
        array (
          'pretty_version' => 'v8.8.3',
          'version' => '8.8.3.0',
          'reference' => '1dc9e88d105699d0fee8bb18890f41b274f6b4c4',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../nunomaduro/collision',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'nunomaduro/termwind' => 
        array (
          'pretty_version' => 'v2.3.3',
          'version' => '2.3.3.0',
          'reference' => '6fb2a640ff502caace8e05fd7be3b503a7e1c017',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../nunomaduro/termwind',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'ondram/ci-detector' => 
        array (
          'pretty_version' => '4.2.0',
          'version' => '4.2.0.0',
          'reference' => '8b0223b5ed235fd377c75fdd1bfcad05c0f168b8',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../ondram/ci-detector',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'paragonie/constant_time_encoding' => 
        array (
          'pretty_version' => 'v3.1.3',
          'version' => '3.1.3.0',
          'reference' => 'd5b01a39b3415c2cd581d3bd3a3575c1ebbd8e77',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../paragonie/constant_time_encoding',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'parsedown/parsedown' => 
        array (
          'pretty_version' => '1.7.5',
          'version' => '1.7.5.0',
          'reference' => '6ca5690b0282544416897376abe056601847ddb3',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../parsedown/parsedown',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'pestphp/pest' => 
        array (
          'pretty_version' => 'v4.3.2',
          'version' => '4.3.2.0',
          'reference' => '3a4329ddc7a2b67c19fca8342a668b39be3ae398',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../pestphp/pest',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'pestphp/pest-plugin' => 
        array (
          'pretty_version' => 'v4.0.0',
          'version' => '4.0.0.0',
          'reference' => '9d4b93d7f73d3f9c3189bb22c220fef271cdf568',
          'type' => 'composer-plugin',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../pestphp/pest-plugin',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'pestphp/pest-plugin-arch' => 
        array (
          'pretty_version' => 'v4.0.0',
          'version' => '4.0.0.0',
          'reference' => '25bb17e37920ccc35cbbcda3b00d596aadf3e58d',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../pestphp/pest-plugin-arch',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'pestphp/pest-plugin-laravel' => 
        array (
          'pretty_version' => 'v4.0.0',
          'version' => '4.0.0.0',
          'reference' => 'e12a07046b826a40b1c8632fd7b80d6b8d7b628e',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../pestphp/pest-plugin-laravel',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'pestphp/pest-plugin-mutate' => 
        array (
          'pretty_version' => 'v4.0.1',
          'version' => '4.0.1.0',
          'reference' => 'd9b32b60b2385e1688a68cc227594738ec26d96c',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../pestphp/pest-plugin-mutate',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'pestphp/pest-plugin-profanity' => 
        array (
          'pretty_version' => 'v4.2.1',
          'version' => '4.2.1.0',
          'reference' => '343cfa6f3564b7e35df0ebb77b7fa97039f72b27',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../pestphp/pest-plugin-profanity',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phar-io/manifest' => 
        array (
          'pretty_version' => '2.0.4',
          'version' => '2.0.4.0',
          'reference' => '54750ef60c58e43759730615a392c31c80e23176',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../phar-io/manifest',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phar-io/version' => 
        array (
          'pretty_version' => '3.2.1',
          'version' => '3.2.1.0',
          'reference' => '4f7fd7836c6f332bb2933569e566a0d6c4cbed74',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../phar-io/version',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpdocumentor/reflection-common' => 
        array (
          'pretty_version' => '2.2.0',
          'version' => '2.2.0.0',
          'reference' => '1d01c49d4ed62f25aa84a747ad35d5a16924662b',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../phpdocumentor/reflection-common',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpdocumentor/reflection-docblock' => 
        array (
          'pretty_version' => '5.6.6',
          'version' => '5.6.6.0',
          'reference' => '5cee1d3dfc2d2aa6599834520911d246f656bcb8',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../phpdocumentor/reflection-docblock',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpdocumentor/type-resolver' => 
        array (
          'pretty_version' => '1.12.0',
          'version' => '1.12.0.0',
          'reference' => '92a98ada2b93d9b201a613cb5a33584dde25f195',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../phpdocumentor/type-resolver',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpoption/phpoption' => 
        array (
          'pretty_version' => '1.9.5',
          'version' => '1.9.5.0',
          'reference' => '75365b91986c2405cf5e1e012c5595cd487a98be',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../phpoption/phpoption',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'phpstan/phpdoc-parser' => 
        array (
          'pretty_version' => '2.3.2',
          'version' => '2.3.2.0',
          'reference' => 'a004701b11273a26cd7955a61d67a7f1e525a45a',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../phpstan/phpdoc-parser',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpstan/phpstan' => 
        array (
          'pretty_version' => '2.1.39',
          'version' => '2.1.39.0',
          'reference' => 'c6f73a2af4cbcd99c931d0fb8f08548cc0fa8224',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../phpstan/phpstan',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-code-coverage' => 
        array (
          'pretty_version' => '12.5.3',
          'version' => '12.5.3.0',
          'reference' => 'b015312f28dd75b75d3422ca37dff2cd1a565e8d',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../phpunit/php-code-coverage',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-file-iterator' => 
        array (
          'pretty_version' => '6.0.1',
          'version' => '6.0.1.0',
          'reference' => '3d1cd096ef6bea4bf2762ba586e35dbd317cbfd5',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../phpunit/php-file-iterator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-invoker' => 
        array (
          'pretty_version' => '6.0.0',
          'version' => '6.0.0.0',
          'reference' => '12b54e689b07a25a9b41e57736dfab6ec9ae5406',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../phpunit/php-invoker',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-text-template' => 
        array (
          'pretty_version' => '5.0.0',
          'version' => '5.0.0.0',
          'reference' => 'e1367a453f0eda562eedb4f659e13aa900d66c53',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../phpunit/php-text-template',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-timer' => 
        array (
          'pretty_version' => '8.0.0',
          'version' => '8.0.0.0',
          'reference' => 'f258ce36aa457f3aa3339f9ed4c81fc66dc8c2cc',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../phpunit/php-timer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/phpunit' => 
        array (
          'pretty_version' => '12.5.8',
          'version' => '12.5.8.0',
          'reference' => '37ddb96c14bfee10304825edbb7e66d341ec6889',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../phpunit/phpunit',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'psr/clock' => 
        array (
          'pretty_version' => '1.0.0',
          'version' => '1.0.0.0',
          'reference' => 'e41a24703d4560fd0acb709162f73b8adfc3aa0d',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../psr/clock',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/clock-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/container' => 
        array (
          'pretty_version' => '2.0.2',
          'version' => '2.0.2.0',
          'reference' => 'c71ecc56dfe541dbd90c5360474fbc405f8d5963',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../psr/container',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/container-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.1|2.0',
          ),
        ),
        'psr/event-dispatcher' => 
        array (
          'pretty_version' => '1.0.0',
          'version' => '1.0.0.0',
          'reference' => 'dbefd12671e8a14ec7f180cab83036ed26714bb0',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../psr/event-dispatcher',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/event-dispatcher-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/http-client' => 
        array (
          'pretty_version' => '1.0.3',
          'version' => '1.0.3.0',
          'reference' => 'bb5906edc1c324c9a05aa0873d40117941e5fa90',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../psr/http-client',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/http-client-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/http-factory' => 
        array (
          'pretty_version' => '1.1.0',
          'version' => '1.1.0.0',
          'reference' => '2b4765fddfe3b508ac62f829e852b1501d3f6e8a',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../psr/http-factory',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/http-factory-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/http-message' => 
        array (
          'pretty_version' => '2.0',
          'version' => '2.0.0.0',
          'reference' => '402d35bcb92c70c026d1a6a9883f06b2ead23d71',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../psr/http-message',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/http-message-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/log' => 
        array (
          'pretty_version' => '3.0.2',
          'version' => '3.0.2.0',
          'reference' => 'f16e1d5863e37f8d8c2a01719f5b34baa2b714d3',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../psr/log',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/log-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0|2.0|3.0',
            1 => '3.0.0',
          ),
        ),
        'psr/simple-cache' => 
        array (
          'pretty_version' => '3.0.0',
          'version' => '3.0.0.0',
          'reference' => '764e0b3939f5ca87cb904f570ef9be2d78a07865',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../psr/simple-cache',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/simple-cache-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0|2.0|3.0',
          ),
        ),
        'psy/psysh' => 
        array (
          'pretty_version' => 'v0.12.20',
          'version' => '0.12.20.0',
          'reference' => '19678eb6b952a03b8a1d96ecee9edba518bb0373',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../psy/psysh',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'ralouphie/getallheaders' => 
        array (
          'pretty_version' => '3.0.3',
          'version' => '3.0.3.0',
          'reference' => '120b605dfeb996808c31b6477290a714d356e822',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../ralouphie/getallheaders',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'ramsey/collection' => 
        array (
          'pretty_version' => '2.1.1',
          'version' => '2.1.1.0',
          'reference' => '344572933ad0181accbf4ba763e85a0306a8c5e2',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../ramsey/collection',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'ramsey/uuid' => 
        array (
          'pretty_version' => '4.9.2',
          'version' => '4.9.2.0',
          'reference' => '8429c78ca35a09f27565311b98101e2826affde0',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../ramsey/uuid',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'rhumsaa/uuid' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => '4.9.2',
          ),
        ),
        'sanmai/di-container' => 
        array (
          'pretty_version' => '0.1.12',
          'version' => '0.1.12.0',
          'reference' => '8b9ad72f6ac1f9e185e5bd060dc9479cb5191d8b',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sanmai/di-container',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sanmai/duoclock' => 
        array (
          'pretty_version' => '0.1.3',
          'version' => '0.1.3.0',
          'reference' => '47461e3ff65b7308635047831a55615652e7be1a',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sanmai/duoclock',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sanmai/later' => 
        array (
          'pretty_version' => '0.1.7',
          'version' => '0.1.7.0',
          'reference' => '72a82d783864bca90412d8a26c1878f8981fee97',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sanmai/later',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sanmai/pipeline' => 
        array (
          'pretty_version' => '7.9',
          'version' => '7.9.0.0',
          'reference' => 'd7046ecce91ae57fca403be694888371a21250eb',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sanmai/pipeline',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/cli-parser' => 
        array (
          'pretty_version' => '4.2.0',
          'version' => '4.2.0.0',
          'reference' => '90f41072d220e5c40df6e8635f5dafba2d9d4d04',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sebastian/cli-parser',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/comparator' => 
        array (
          'pretty_version' => '7.1.4',
          'version' => '7.1.4.0',
          'reference' => '6a7de5df2e094f9a80b40a522391a7e6022df5f6',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sebastian/comparator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/complexity' => 
        array (
          'pretty_version' => '5.0.0',
          'version' => '5.0.0.0',
          'reference' => 'bad4316aba5303d0221f43f8cee37eb58d384bbb',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sebastian/complexity',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/diff' => 
        array (
          'pretty_version' => '7.0.0',
          'version' => '7.0.0.0',
          'reference' => '7ab1ea946c012266ca32390913653d844ecd085f',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sebastian/diff',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/environment' => 
        array (
          'pretty_version' => '8.0.3',
          'version' => '8.0.3.0',
          'reference' => '24a711b5c916efc6d6e62aa65aa2ec98fef77f68',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sebastian/environment',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/exporter' => 
        array (
          'pretty_version' => '7.0.2',
          'version' => '7.0.2.0',
          'reference' => '016951ae10980765e4e7aee491eb288c64e505b7',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sebastian/exporter',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/global-state' => 
        array (
          'pretty_version' => '8.0.2',
          'version' => '8.0.2.0',
          'reference' => 'ef1377171613d09edd25b7816f05be8313f9115d',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sebastian/global-state',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/lines-of-code' => 
        array (
          'pretty_version' => '4.0.0',
          'version' => '4.0.0.0',
          'reference' => '97ffee3bcfb5805568d6af7f0f893678fc076d2f',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sebastian/lines-of-code',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/object-enumerator' => 
        array (
          'pretty_version' => '7.0.0',
          'version' => '7.0.0.0',
          'reference' => '1effe8e9b8e068e9ae228e542d5d11b5d16db894',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sebastian/object-enumerator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/object-reflector' => 
        array (
          'pretty_version' => '5.0.0',
          'version' => '5.0.0.0',
          'reference' => '4bfa827c969c98be1e527abd576533293c634f6a',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sebastian/object-reflector',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/recursion-context' => 
        array (
          'pretty_version' => '7.0.1',
          'version' => '7.0.1.0',
          'reference' => '0b01998a7d5b1f122911a66bebcb8d46f0c82d8c',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sebastian/recursion-context',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/type' => 
        array (
          'pretty_version' => '6.0.3',
          'version' => '6.0.3.0',
          'reference' => 'e549163b9760b8f71f191651d22acf32d56d6d4d',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sebastian/type',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/version' => 
        array (
          'pretty_version' => '6.0.0',
          'version' => '6.0.0.0',
          'reference' => '3e6ccf7657d4f0a59200564b08cead899313b53c',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../sebastian/version',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'shalvah/upgrader' => 
        array (
          'pretty_version' => '0.6.0',
          'version' => '0.6.0.0',
          'reference' => 'd95ed17fe9f5e1ee7d47ad835595f1af080a867f',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../shalvah/upgrader',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'spatie/browsershot' => 
        array (
          'pretty_version' => '5.2.2',
          'version' => '5.2.2.0',
          'reference' => 'c07bbd63f4cb698a0b163995a5851ecf33a16f90',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../spatie/browsershot',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'spatie/crawler' => 
        array (
          'pretty_version' => '8.4.7',
          'version' => '8.4.7.0',
          'reference' => '67cbd569437d0e35b1332c5f21d009cac8b4a37b',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../spatie/crawler',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'spatie/laravel-package-tools' => 
        array (
          'pretty_version' => '1.92.7',
          'version' => '1.92.7.0',
          'reference' => 'f09a799850b1ed765103a4f0b4355006360c49a5',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../spatie/laravel-package-tools',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'spatie/laravel-sitemap' => 
        array (
          'pretty_version' => '7.3.8',
          'version' => '7.3.8.0',
          'reference' => '9ff614d4834ada564aed5ed88507c9e5baab8e51',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../spatie/laravel-sitemap',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'spatie/once' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => '*',
          ),
        ),
        'spatie/robots-txt' => 
        array (
          'pretty_version' => '2.5.3',
          'version' => '2.5.3.0',
          'reference' => 'edb91c798ec70583d41c131019da45fa167af5e8',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../spatie/robots-txt',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'spatie/temporary-directory' => 
        array (
          'pretty_version' => '2.3.1',
          'version' => '2.3.1.0',
          'reference' => '662e481d6ec07ef29fd05010433428851a42cd07',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../spatie/temporary-directory',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'staabm/side-effects-detector' => 
        array (
          'pretty_version' => '1.0.5',
          'version' => '1.0.5.0',
          'reference' => 'd8334211a140ce329c13726d4a715adbddd0a163',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../staabm/side-effects-detector',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'stripe/stripe-php' => 
        array (
          'pretty_version' => 'v17.6.0',
          'version' => '17.6.0.0',
          'reference' => 'a6219df5df1324a0d3f1da25fb5e4b8a3307ea16',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../stripe/stripe-php',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/clock' => 
        array (
          'pretty_version' => 'v8.0.0',
          'version' => '8.0.0.0',
          'reference' => '832119f9b8dbc6c8e6f65f30c5969eca1e88764f',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/clock',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/console' => 
        array (
          'pretty_version' => 'v7.4.4',
          'version' => '7.4.4.0',
          'reference' => '41e38717ac1dd7a46b6bda7d6a82af2d98a78894',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/console',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/css-selector' => 
        array (
          'pretty_version' => 'v8.0.0',
          'version' => '8.0.0.0',
          'reference' => '6225bd458c53ecdee056214cb4a2ffaf58bd592b',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/css-selector',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/deprecation-contracts' => 
        array (
          'pretty_version' => 'v3.6.0',
          'version' => '3.6.0.0',
          'reference' => '63afe740e99a13ba87ec199bb07bbdee937a5b62',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/deprecation-contracts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/dom-crawler' => 
        array (
          'pretty_version' => 'v8.0.4',
          'version' => '8.0.4.0',
          'reference' => 'fd78228fa362b41729173183493f46b1df49485f',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/dom-crawler',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/error-handler' => 
        array (
          'pretty_version' => 'v7.4.4',
          'version' => '7.4.4.0',
          'reference' => '8da531f364ddfee53e36092a7eebbbd0b775f6b8',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/error-handler',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/event-dispatcher' => 
        array (
          'pretty_version' => 'v8.0.4',
          'version' => '8.0.4.0',
          'reference' => '99301401da182b6cfaa4700dbe9987bb75474b47',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/event-dispatcher',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/event-dispatcher-contracts' => 
        array (
          'pretty_version' => 'v3.6.0',
          'version' => '3.6.0.0',
          'reference' => '59eb412e93815df44f05f342958efa9f46b1e586',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/event-dispatcher-contracts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/event-dispatcher-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '2.0|3.0',
          ),
        ),
        'symfony/filesystem' => 
        array (
          'pretty_version' => 'v8.0.1',
          'version' => '8.0.1.0',
          'reference' => 'd937d400b980523dc9ee946bb69972b5e619058d',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/filesystem',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/finder' => 
        array (
          'pretty_version' => 'v7.4.5',
          'version' => '7.4.5.0',
          'reference' => 'ad4daa7c38668dcb031e63bc99ea9bd42196a2cb',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/finder',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/http-foundation' => 
        array (
          'pretty_version' => 'v7.4.5',
          'version' => '7.4.5.0',
          'reference' => '446d0db2b1f21575f1284b74533e425096abdfb6',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/http-foundation',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/http-kernel' => 
        array (
          'pretty_version' => 'v7.4.5',
          'version' => '7.4.5.0',
          'reference' => '229eda477017f92bd2ce7615d06222ec0c19e82a',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/http-kernel',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/mailer' => 
        array (
          'pretty_version' => 'v7.4.4',
          'version' => '7.4.4.0',
          'reference' => '7b750074c40c694ceb34cb926d6dffee231c5cd6',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/mailer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/mime' => 
        array (
          'pretty_version' => 'v7.4.5',
          'version' => '7.4.5.0',
          'reference' => 'b18c7e6e9eee1e19958138df10412f3c4c316148',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/mime',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-ctype' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => 'a3cc8b044a6ea513310cbd48ef7333b384945638',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/polyfill-ctype',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-intl-grapheme' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '380872130d3a5dd3ace2f4010d95125fde5d5c70',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/polyfill-intl-grapheme',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-intl-icu' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => 'bfc8fa13dbaf21d69114b0efcd72ab700fb04d0c',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/polyfill-intl-icu',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-intl-idn' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '9614ac4d8061dc257ecc64cba1b140873dce8ad3',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/polyfill-intl-idn',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-intl-normalizer' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '3833d7255cc303546435cb650316bff708a1c75c',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/polyfill-intl-normalizer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-mbstring' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '6d857f4d76bd4b343eac26d6b539585d2bc56493',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/polyfill-mbstring',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-php80' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '0cc9dd0f17f61d8131e7df6b84bd344899fe2608',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/polyfill-php80',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-php83' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '17f6f9a6b1735c0f163024d959f700cfbc5155e5',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/polyfill-php83',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-php84' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => 'd8ced4d875142b6a7426000426b8abc631d6b191',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/polyfill-php84',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-php85' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => 'd4e5fcd4ab3d998ab16c0db48e6cbb9a01993f91',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/polyfill-php85',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-uuid' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '21533be36c24be3f4b1669c4725c7d1d2bab4ae2',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/polyfill-uuid',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/process' => 
        array (
          'pretty_version' => 'v7.4.5',
          'version' => '7.4.5.0',
          'reference' => '608476f4604102976d687c483ac63a79ba18cc97',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/process',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/routing' => 
        array (
          'pretty_version' => 'v7.4.4',
          'version' => '7.4.4.0',
          'reference' => '0798827fe2c79caeed41d70b680c2c3507d10147',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/routing',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/service-contracts' => 
        array (
          'pretty_version' => 'v3.6.1',
          'version' => '3.6.1.0',
          'reference' => '45112560a3ba2d715666a509a0bc9521d10b6c43',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/service-contracts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/string' => 
        array (
          'pretty_version' => 'v8.0.4',
          'version' => '8.0.4.0',
          'reference' => '758b372d6882506821ed666032e43020c4f57194',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/string',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/translation' => 
        array (
          'pretty_version' => 'v8.0.4',
          'version' => '8.0.4.0',
          'reference' => 'db70c8ce7db74fd2da7b1d268db46b2a8ce32c10',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/translation',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/translation-contracts' => 
        array (
          'pretty_version' => 'v3.6.1',
          'version' => '3.6.1.0',
          'reference' => '65a8bc82080447fae78373aa10f8d13b38338977',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/translation-contracts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/translation-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '2.3|3.0',
          ),
        ),
        'symfony/uid' => 
        array (
          'pretty_version' => 'v7.4.4',
          'version' => '7.4.4.0',
          'reference' => '7719ce8aba76be93dfe249192f1fbfa52c588e36',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/uid',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/var-dumper' => 
        array (
          'pretty_version' => 'v7.4.4',
          'version' => '7.4.4.0',
          'reference' => '0e4769b46a0c3c62390d124635ce59f66874b282',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/var-dumper',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/var-exporter' => 
        array (
          'pretty_version' => 'v7.4.0',
          'version' => '7.4.0.0',
          'reference' => '03a60f169c79a28513a78c967316fbc8bf17816f',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/var-exporter',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/yaml' => 
        array (
          'pretty_version' => 'v7.4.1',
          'version' => '7.4.1.0',
          'reference' => '24dd4de28d2e3988b311751ac49e684d783e2345',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../symfony/yaml',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'ta-tikoma/phpunit-architecture-test' => 
        array (
          'pretty_version' => '0.8.6',
          'version' => '0.8.6.0',
          'reference' => 'ad48430b92901fd7d003fdaf2d7b139f96c0906e',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../ta-tikoma/phpunit-architecture-test',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'thecodingmachine/safe' => 
        array (
          'pretty_version' => 'v3.3.0',
          'version' => '3.3.0.0',
          'reference' => '2cdd579eeaa2e78e51c7509b50cc9fb89a956236',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../thecodingmachine/safe',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'theseer/tokenizer' => 
        array (
          'pretty_version' => '2.0.1',
          'version' => '2.0.1.0',
          'reference' => '7989e43bf381af0eac72e4f0ca5bcbfa81658be4',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../theseer/tokenizer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'tightenco/ziggy' => 
        array (
          'pretty_version' => 'v2.6.0',
          'version' => '2.6.0.0',
          'reference' => 'cccc6035c109daab03a33926b3a8499bedbed01f',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../tightenco/ziggy',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'tijsverkoyen/css-to-inline-styles' => 
        array (
          'pretty_version' => 'v2.4.0',
          'version' => '2.4.0.0',
          'reference' => 'f0292ccf0ec75843d65027214426b6b163b48b41',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../tijsverkoyen/css-to-inline-styles',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'vlucas/phpdotenv' => 
        array (
          'pretty_version' => 'v5.6.3',
          'version' => '5.6.3.0',
          'reference' => '955e7815d677a3eaa7075231212f2110983adecc',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../vlucas/phpdotenv',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'voku/portable-ascii' => 
        array (
          'pretty_version' => '2.0.3',
          'version' => '2.0.3.0',
          'reference' => 'b1d923f88091c6bf09699efcd7c8a1b1bfd7351d',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../voku/portable-ascii',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'webmozart/assert' => 
        array (
          'pretty_version' => '2.1.2',
          'version' => '2.1.2.0',
          'reference' => 'ce6a2f100c404b2d32a1dd1270f9b59ad4f57649',
          'type' => 'library',
          'install_path' => '/Users/sood/dev/heatware/laravel-react-starter/vendor/composer/../webmozart/assert',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
      ),
    ),
  ),
  'executedFilesHashes' => 
  array (
    '/Users/sood/dev/heatware/laravel-react-starter/vendor/larastan/larastan/bootstrap.php' => '5a3eacbf63b3e41659adfee92facededf8e020a932800f93c9a8b0e67f235805',
    'phar:///Users/sood/dev/heatware/laravel-react-starter/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Attribute85.php' => 'cb8b31e82c61ce197871c9e8a6f122256751f2ab606dd2be90846d4fa5f8933e',
    'phar:///Users/sood/dev/heatware/laravel-react-starter/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionAttribute.php' => 'c0068e383717870a304781d462f7e2afe1c6f24e9133851852a2aca96b4fa26f',
    'phar:///Users/sood/dev/heatware/laravel-react-starter/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionIntersectionType.php' => '65fe0a8bc6fe285d8ddc8798ab5b9299920af70db5ad74596bc08df823e7c5d9',
    'phar:///Users/sood/dev/heatware/laravel-react-starter/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionUnionType.php' => '1e2fe940e4ba4e00d9ee6adb2af3ee1bf333e6f8afe61c61deb038886d293427',
  ),
  'phpExtensions' => 
  array (
    0 => 'Core',
    1 => 'FFI',
    2 => 'PDO',
    3 => 'PDO_ODBC',
    4 => 'Phar',
    5 => 'Reflection',
    6 => 'SPL',
    7 => 'SimpleXML',
    8 => 'Zend OPcache',
    9 => 'bcmath',
    10 => 'bz2',
    11 => 'calendar',
    12 => 'ctype',
    13 => 'curl',
    14 => 'date',
    15 => 'dba',
    16 => 'dom',
    17 => 'exif',
    18 => 'fileinfo',
    19 => 'filter',
    20 => 'ftp',
    21 => 'gd',
    22 => 'gettext',
    23 => 'gmp',
    24 => 'hash',
    25 => 'iconv',
    26 => 'intl',
    27 => 'json',
    28 => 'ldap',
    29 => 'libxml',
    30 => 'mbstring',
    31 => 'mysqli',
    32 => 'mysqlnd',
    33 => 'odbc',
    34 => 'openssl',
    35 => 'pcntl',
    36 => 'pcre',
    37 => 'pdo_dblib',
    38 => 'pdo_mysql',
    39 => 'pdo_pgsql',
    40 => 'pdo_sqlite',
    41 => 'pgsql',
    42 => 'posix',
    43 => 'random',
    44 => 'readline',
    45 => 'redis',
    46 => 'session',
    47 => 'shmop',
    48 => 'snmp',
    49 => 'soap',
    50 => 'sockets',
    51 => 'sodium',
    52 => 'sqlite3',
    53 => 'standard',
    54 => 'sysvmsg',
    55 => 'sysvsem',
    56 => 'sysvshm',
    57 => 'tidy',
    58 => 'tokenizer',
    59 => 'xml',
    60 => 'xmlreader',
    61 => 'xmlwriter',
    62 => 'xsl',
    63 => 'zip',
    64 => 'zlib',
  ),
  'stubFiles' => 
  array (
  ),
  'level' => '5',
),
	'projectExtensionFiles' => array (
),
	'errorsCallback' => static function (): array { return array (
  '/Users/sood/dev/heatware/laravel-react-starter/app/Console/Commands/CheckIncompletePayments.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to an undefined method Illuminate\\Database\\Eloquent\\Model::notifications().',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Console/Commands/CheckIncompletePayments.php',
       'line' => 45,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Console/Commands/CheckIncompletePayments.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 45,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to an undefined method Illuminate\\Database\\Eloquent\\Model::notify().',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Console/Commands/CheckIncompletePayments.php',
       'line' => 57,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Console/Commands/CheckIncompletePayments.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 57,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Helpers/QueryHelper.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Helpers\\QueryHelper::dateExpression() should return Illuminate\\Database\\Query\\Expression but returns Illuminate\\Contracts\\Database\\Query\\Expression.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Helpers/QueryHelper.php',
       'line' => 23,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Helpers/QueryHelper.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 23,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Pagination\\AbstractPaginator<int,App\\Models\\AuditLog>::through() contains unresolvable type.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
       'line' => 39,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 39,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
       'line' => 42,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 42,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$email.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
       'line' => 43,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 43,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
       'line' => 71,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 71,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$email.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
       'line' => 72,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 72,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$email.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
       'line' => 120,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 120,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Using nullsafe property access "?->email" on left side of ?? is unnecessary. Use -> instead.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
       'line' => 120,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 120,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Coalesce',
       'identifier' => 'nullsafe.neverNull',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Database\\Eloquent\\Collection<int,App\\Models\\AuditLog>::map() contains unresolvable type.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'line' => 31,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 23,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'line' => 34,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 34,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Database\\Eloquent\\Collection<int,Illuminate\\Database\\Eloquent\\Model>::map() contains unresolvable type.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'line' => 63,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 63,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'line' => 64,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 64,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$stripe_price.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'line' => 65,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 65,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$stripe_product.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'line' => 66,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 66,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$quantity.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'line' => 67,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 67,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$stripe_price.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'line' => 68,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 68,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Using nullsafe property access "?->name" on left side of ?? is unnecessary. Use -> instead.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'line' => 89,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 89,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Coalesce',
       'identifier' => 'nullsafe.neverNull',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Using nullsafe property access "?->email" on left side of ?? is unnecessary. Use -> instead.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'line' => 90,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 90,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Coalesce',
       'identifier' => 'nullsafe.neverNull',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property App\\Models\\User::$count.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
       'line' => 43,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 43,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property App\\Models\\User::$date.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
       'line' => 43,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 43,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Database\\Eloquent\\Collection<int,App\\Models\\User>::map() contains unresolvable type.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
       'line' => 43,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 38,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Database\\Eloquent\\Collection<int,App\\Models\\AuditLog>::map() contains unresolvable type.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
       'line' => 51,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 47,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
       'line' => 54,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 54,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$email.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 55,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Pagination\\AbstractPaginator<int,App\\Models\\User>::through() contains unresolvable type.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
       'line' => 46,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 46,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toISOString() on string.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
       'line' => 51,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 51,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toISOString() on string.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
       'line' => 52,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 52,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toISOString() on string.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
       'line' => 101,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 101,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toISOString() on string.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
       'line' => 102,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 102,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Database\\Eloquent\\Collection<int,Illuminate\\Database\\Eloquent\\Model>::map() contains unresolvable type.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 31,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 26,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 32,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 32,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$url.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 33,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 33,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$events.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 34,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 34,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$description.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 35,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 35,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$active.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 36,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 36,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$deliveries_count.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 37,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 37,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$created_at.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 38,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 38,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 66,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 66,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$secret.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 67,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 67,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 76,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 76,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$url.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 77,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 77,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$events.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 78,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 78,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$description.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 79,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 79,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    14 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$active.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 80,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 80,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    15 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$secret.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 81,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 81,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    16 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$created_at.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 82,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 82,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    17 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to an undefined method Illuminate\\Database\\Eloquent\\Model::deliveries().',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 114,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 114,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    18 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $endpoint of method App\\Services\\WebhookService::dispatchToEndpoint() expects App\\Models\\WebhookEndpoint, Illuminate\\Database\\Eloquent\\Model given.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'line' => 136,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 136,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/SocialAuthController.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method driver() on an unknown class Laravel\\Socialite\\Facades\\Socialite.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/SocialAuthController.php',
       'line' => 52,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/SocialAuthController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 52,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method driver() on an unknown class Laravel\\Socialite\\Facades\\Socialite.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/SocialAuthController.php',
       'line' => 71,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/SocialAuthController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 71,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to function method_exists() with App\\Models\\User and \'updateLastLogin\' will always evaluate to true.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/SocialAuthController.php',
       'line' => 86,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/SocialAuthController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 86,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.alreadyNarrowedType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Laravel\\Cashier\\Payment::$id.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
       'line' => 47,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 47,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Support\\Collection<int,Laravel\\Cashier\\Invoice>::map() contains unresolvable type.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 53,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Laravel\\Cashier\\Invoice::$id.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
       'line' => 56,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 56,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Laravel\\Cashier\\Invoice::$status.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
       'line' => 59,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 59,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to an undefined method Laravel\\Cashier\\Invoice::invoicePdf().',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
       'line' => 60,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 60,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toISOString() on string.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
       'line' => 78,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 78,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/PricingController.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toISOString() on string.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/PricingController.php',
       'line' => 53,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/PricingController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 53,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/HandleInertiaRequests.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toISOString() on string.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/HandleInertiaRequests.php',
       'line' => 49,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/HandleInertiaRequests.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 49,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/RequestIdMiddleware.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method setTag() on an unknown class Sentry\\State\\Scope.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/RequestIdMiddleware.php',
       'line' => 25,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/RequestIdMiddleware.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 25,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Function Sentry\\configureScope not found.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/RequestIdMiddleware.php',
       'line' => 25,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/RequestIdMiddleware.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 25,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter $scope of anonymous function has invalid type Sentry\\State\\Scope.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/RequestIdMiddleware.php',
       'line' => 25,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/RequestIdMiddleware.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 25,
       'nodeType' => 'PhpParser\\Node\\Expr\\ArrowFunction',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/DispatchWebhookJob.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$active.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/DispatchWebhookJob.php',
       'line' => 30,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/DispatchWebhookJob.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 30,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$secret.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/DispatchWebhookJob.php',
       'line' => 36,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/DispatchWebhookJob.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 36,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$url.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/DispatchWebhookJob.php',
       'line' => 52,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/DispatchWebhookJob.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 52,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$url.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/DispatchWebhookJob.php',
       'line' => 69,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/DispatchWebhookJob.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 69,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/SocialAccount.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method isPast() on string.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Models/SocialAccount.php',
       'line' => 68,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Models/SocialAccount.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 68,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/UserSetting.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\UserSetting::setValue() should return static(App\\Models\\UserSetting) but returns App\\Models\\UserSetting.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Models/UserSetting.php',
       'line' => 82,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Models/UserSetting.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 82,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AuditService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Using nullsafe method call on non-nullable type Symfony\\Component\\HttpFoundation\\ParameterBag. Use -> instead.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AuditService.php',
       'line' => 59,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AuditService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 59,
       'nodeType' => 'PhpParser\\Node\\Expr\\NullsafeMethodCall',
       'identifier' => 'nullsafe.neverNull',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Using nullsafe property access on non-nullable type Illuminate\\Http\\Request. Use -> instead.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AuditService.php',
       'line' => 59,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AuditService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 59,
       'nodeType' => 'PhpParser\\Node\\Expr\\NullsafePropertyFetch',
       'identifier' => 'nullsafe.neverNull',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/BillingService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method isFuture() on string.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/BillingService.php',
       'line' => 160,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/BillingService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 160,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toISOString() on string.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/BillingService.php',
       'line' => 164,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/BillingService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 164,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/FeatureFlagService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Database\\Eloquent\\Collection<int,App\\Models\\FeatureFlagOverride>::map() contains unresolvable type.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/FeatureFlagService.php',
       'line' => 188,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/FeatureFlagService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 176,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property App\\Models\\FeatureFlagOverride::$name.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/FeatureFlagService.php',
       'line' => 190,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/FeatureFlagService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 190,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property App\\Models\\FeatureFlagOverride::$email.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/FeatureFlagService.php',
       'line' => 191,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/FeatureFlagService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 191,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/HealthCheckService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Strict comparison using === between float and 0 will always evaluate to false.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/HealthCheckService.php',
       'line' => 106,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/HealthCheckService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 106,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Identical',
       'identifier' => 'identical.alwaysFalse',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/PlanLimitService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method isFuture() on string.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/PlanLimitService.php',
       'line' => 48,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/PlanLimitService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 48,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter $socialUser of method App\\Services\\SocialAuthService::findOrCreateUser() has invalid type Laravel\\Socialite\\Contracts\\User.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'line' => 24,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 24,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method getId() on an unknown class Laravel\\Socialite\\Contracts\\User.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'line' => 28,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 28,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\SocialAuthService::findOrCreateUser() should return App\\Models\\User but returns Illuminate\\Database\\Eloquent\\Model|null.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'line' => 32,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 32,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method getEmail() on an unknown class Laravel\\Socialite\\Contracts\\User.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'line' => 36,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 36,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method getName() on an unknown class Laravel\\Socialite\\Contracts\\User.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'line' => 44,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 44,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method getNickname() on an unknown class Laravel\\Socialite\\Contracts\\User.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'line' => 44,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 44,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method getEmail() on an unknown class Laravel\\Socialite\\Contracts\\User.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'line' => 45,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 45,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter $socialUser of method App\\Services\\SocialAuthService::linkSocialAccount() has invalid type Laravel\\Socialite\\Contracts\\User.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 55,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method getId() on an unknown class Laravel\\Socialite\\Contracts\\User.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'line' => 63,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 63,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $token on an unknown class Laravel\\Socialite\\Contracts\\User.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'line' => 64,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 64,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $refreshToken on an unknown class Laravel\\Socialite\\Contracts\\User.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'line' => 65,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 65,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $expiresIn on an unknown class Laravel\\Socialite\\Contracts\\User.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'line' => 66,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 66,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $expiresIn on an unknown class Laravel\\Socialite\\Contracts\\User.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'line' => 67,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 67,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/WebhookService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $haystack of function in_array expects array, string given.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/WebhookService.php',
       'line' => 20,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/WebhookService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 20,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/routes/api.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toISOString() on string.',
       'file' => '/Users/sood/dev/heatware/laravel-react-starter/routes/api.php',
       'line' => 36,
       'canBeIgnored' => true,
       'filePath' => '/Users/sood/dev/heatware/laravel-react-starter/routes/api.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 36,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
); },
	'locallyIgnoredErrorsCallback' => static function (): array { return array (
); },
	'linesToIgnore' => array (
),
	'unmatchedLineIgnores' => array (
),
	'collectedDataCallback' => static function (): array { return array (
  '/Users/sood/dev/heatware/laravel-react-starter/app/Enums/AdminCacheKey.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Enums\\AdminCacheKey',
        1 => 'featureFlagsUser',
        2 => 'App\\Enums\\AdminCacheKey',
      ),
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 57,
      ),
      1 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 67,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Controllers\\Admin\\AdminBillingController',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminFeatureFlagController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Controllers\\Admin\\AdminFeatureFlagController',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminHealthController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Controllers\\Admin\\AdminHealthController',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminImpersonationController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Controllers\\Admin\\AdminImpersonationController',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Controllers\\Admin\\AdminUsersController',
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 123,
      ),
      1 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 163,
      ),
      2 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 183,
      ),
      3 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 194,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/NotificationController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 53,
      ),
      1 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 67,
      ),
      2 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 88,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/TokenController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 69,
      ),
      1 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 70,
      ),
      2 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 71,
      ),
      3 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 99,
      ),
      4 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 100,
      ),
      5 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 101,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 146,
      ),
      1 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 147,
      ),
      2 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 148,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/AuthenticatedSessionController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Controllers\\Auth\\AuthenticatedSessionController',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/ConfirmablePasswordController.php' => 
  array (
    'Larastan\\Larastan\\Collectors\\UsedTranslationFunctionCollector' => 
    array (
      0 => 
      array (
        0 => 'auth.password',
        1 => 33,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/NewPasswordController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'event',
        1 => 54,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/RegisteredUserController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Controllers\\Auth\\RegisteredUserController',
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'event',
        1 => 78,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/SocialAuthController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Controllers\\Auth\\SocialAuthController',
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'abort',
        1 => 39,
      ),
      1 => 
      array (
        0 => 'abort',
        1 => 44,
      ),
      2 => 
      array (
        0 => 'abort',
        1 => 49,
      ),
      3 => 
      array (
        0 => 'abort',
        1 => 62,
      ),
      4 => 
      array (
        0 => 'abort',
        1 => 67,
      ),
      5 => 
      array (
        0 => 'abort',
        1 => 100,
      ),
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Connection',
        1 => 'transaction',
        2 => 106,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/TwoFactorChallengeController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Support\\Facades\\Auth',
        1 => 'loginUsingId',
        2 => 63,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/VerifyEmailController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'event',
        1 => 22,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Controllers\\Billing\\BillingController',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/PricingController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Controllers\\Billing\\PricingController',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/StripeWebhookController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Laravel\\Cashier\\Http\\Controllers\\WebhookController',
        1 => '__construct',
        2 => 25,
      ),
      1 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 162,
      ),
      2 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 163,
      ),
      3 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 164,
      ),
      4 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 165,
      ),
      5 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 166,
      ),
      6 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 167,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/SubscriptionController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Controllers\\Billing\\SubscriptionController',
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 325,
      ),
      1 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 326,
      ),
      2 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 327,
      ),
      3 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 328,
      ),
      4 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 329,
      ),
      5 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 330,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Controller.php' => 
  array (
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Foundation\\Auth\\Access\\AuthorizesRequests',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/HealthCheckController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Controllers\\HealthCheckController',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ProfileController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Controllers\\ProfileController',
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Jobs\\CancelOrphanedStripeSubscription',
        1 => 'dispatch',
        2 => 79,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/TwoFactorController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'abort',
        1 => 85,
      ),
      1 => 
      array (
        0 => 'abort',
        1 => 98,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Webhook/IncomingWebhookController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Controllers\\Webhook\\IncomingWebhookController',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/EnsureIsAdmin.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Middleware\\EnsureIsAdmin',
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'abort',
        1 => 24,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/EnsureSubscribed.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Middleware\\EnsureSubscribed',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/HandleInertiaRequests.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Middleware\\HandleInertiaRequests',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/SecurityHeaders.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Support\\Facades\\Vite',
        1 => 'useCspNonce',
        2 => 15,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/VerifyWebhookSignature.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'abort',
        1 => 17,
      ),
      1 => 
      array (
        0 => 'abort',
        1 => 23,
      ),
      2 => 
      array (
        0 => 'abort',
        1 => 42,
      ),
      3 => 
      array (
        0 => 'abort',
        1 => 49,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminAuditLogIndexRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Admin\\AdminAuditLogIndexRequest',
        1 => 'rules',
        2 => 'App\\Http\\Requests\\Admin\\AdminAuditLogIndexRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminExportRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Admin\\AdminExportRequest',
        1 => 'rules',
        2 => 'App\\Http\\Requests\\Admin\\AdminExportRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminFeatureFlagRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagRequest',
        1 => 'rules',
        2 => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminFeatureFlagUserRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagUserRequest',
        1 => 'rules',
        2 => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagUserRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminSubscriptionIndexRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Admin\\AdminSubscriptionIndexRequest',
        1 => 'rules',
        2 => 'App\\Http\\Requests\\Admin\\AdminSubscriptionIndexRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminUserIndexRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Admin\\AdminUserIndexRequest',
        1 => 'rules',
        2 => 'App\\Http\\Requests\\Admin\\AdminUserIndexRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Api/CreateTokenRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Api\\CreateTokenRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\Api\\CreateTokenRequest',
      ),
      1 => 
      array (
        0 => 'App\\Http\\Requests\\Api\\CreateTokenRequest',
        1 => 'messages',
        2 => 'App\\Http\\Requests\\Api\\CreateTokenRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Api/UpdateSettingRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Api\\UpdateSettingRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\Api\\UpdateSettingRequest',
      ),
      1 => 
      array (
        0 => 'App\\Http\\Requests\\Api\\UpdateSettingRequest',
        1 => 'messages',
        2 => 'App\\Http\\Requests\\Api\\UpdateSettingRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Auth/LoginRequest.php' => 
  array (
    'Larastan\\Larastan\\Collectors\\UsedTranslationFunctionCollector' => 
    array (
      0 => 
      array (
        0 => 'auth.failed',
        1 => 55,
      ),
      1 => 
      array (
        0 => 'auth.throttle',
        1 => 84,
      ),
    ),
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Auth\\LoginRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\Auth\\LoginRequest',
      ),
      1 => 
      array (
        0 => 'App\\Http\\Requests\\Auth\\LoginRequest',
        1 => 'rules',
        2 => 'App\\Http\\Requests\\Auth\\LoginRequest',
      ),
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'event',
        1 => 79,
      ),
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Support\\Facades\\RateLimiter',
        1 => 'hit',
        2 => 52,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Auth/PasswordUpdateRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Auth\\PasswordUpdateRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\Auth\\PasswordUpdateRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Auth/RegisterRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Auth\\RegisterRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\Auth\\RegisterRequest',
      ),
      1 => 
      array (
        0 => 'App\\Http\\Requests\\Auth\\RegisterRequest',
        1 => 'messages',
        2 => 'App\\Http\\Requests\\Auth\\RegisterRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/CancelSubscriptionRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Billing\\CancelSubscriptionRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\Billing\\CancelSubscriptionRequest',
      ),
      1 => 
      array (
        0 => 'App\\Http\\Requests\\Billing\\CancelSubscriptionRequest',
        1 => 'rules',
        2 => 'App\\Http\\Requests\\Billing\\CancelSubscriptionRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/Concerns/HasPriceValidation.php' => 
  array (
    'PHPStan\\Rules\\Traits\\TraitDeclarationCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Billing\\Concerns\\HasPriceValidation',
        1 => 5,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/SubscribeRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Billing\\SubscribeRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\Billing\\SubscribeRequest',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Billing\\Concerns\\HasPriceValidation',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/SwapPlanRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Billing\\SwapPlanRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\Billing\\SwapPlanRequest',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Billing\\Concerns\\HasPriceValidation',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/UpdatePaymentMethodRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Billing\\UpdatePaymentMethodRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\Billing\\UpdatePaymentMethodRequest',
      ),
      1 => 
      array (
        0 => 'App\\Http\\Requests\\Billing\\UpdatePaymentMethodRequest',
        1 => 'rules',
        2 => 'App\\Http\\Requests\\Billing\\UpdatePaymentMethodRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/UpdateQuantityRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Billing\\UpdateQuantityRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\Billing\\UpdateQuantityRequest',
      ),
      1 => 
      array (
        0 => 'App\\Http\\Requests\\Billing\\UpdateQuantityRequest',
        1 => 'rules',
        2 => 'App\\Http\\Requests\\Billing\\UpdateQuantityRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/DeleteAccountRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\DeleteAccountRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\DeleteAccountRequest',
      ),
      1 => 
      array (
        0 => 'App\\Http\\Requests\\DeleteAccountRequest',
        1 => 'rules',
        2 => 'App\\Http\\Requests\\DeleteAccountRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/ExportRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\ExportRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\ExportRequest',
      ),
      1 => 
      array (
        0 => 'App\\Http\\Requests\\ExportRequest',
        1 => 'rules',
        2 => 'App\\Http\\Requests\\ExportRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/ProfileUpdateRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\ProfileUpdateRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\ProfileUpdateRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/TwoFactor/ConfirmTwoFactorRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\TwoFactor\\ConfirmTwoFactorRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\TwoFactor\\ConfirmTwoFactorRequest',
      ),
      1 => 
      array (
        0 => 'App\\Http\\Requests\\TwoFactor\\ConfirmTwoFactorRequest',
        1 => 'rules',
        2 => 'App\\Http\\Requests\\TwoFactor\\ConfirmTwoFactorRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/TwoFactor/DisableTwoFactorRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\TwoFactor\\DisableTwoFactorRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\TwoFactor\\DisableTwoFactorRequest',
      ),
      1 => 
      array (
        0 => 'App\\Http\\Requests\\TwoFactor\\DisableTwoFactorRequest',
        1 => 'rules',
        2 => 'App\\Http\\Requests\\TwoFactor\\DisableTwoFactorRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/TwoFactor/TwoFactorChallengeRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\TwoFactor\\TwoFactorChallengeRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\TwoFactor\\TwoFactorChallengeRequest',
      ),
      1 => 
      array (
        0 => 'App\\Http\\Requests\\TwoFactor\\TwoFactorChallengeRequest',
        1 => 'rules',
        2 => 'App\\Http\\Requests\\TwoFactor\\TwoFactorChallengeRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Webhook/CreateWebhookEndpointRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Webhook\\CreateWebhookEndpointRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\Webhook\\CreateWebhookEndpointRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Webhook/UpdateWebhookEndpointRequest.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Http\\Requests\\Webhook\\UpdateWebhookEndpointRequest',
        1 => 'authorize',
        2 => 'App\\Http\\Requests\\Webhook\\UpdateWebhookEndpointRequest',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/CancelOrphanedStripeSubscription.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Jobs\\CancelOrphanedStripeSubscription',
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Foundation\\Queue\\Queueable',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/DispatchWebhookJob.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Jobs\\DispatchWebhookJob',
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Foundation\\Queue\\Queueable',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/PersistAuditLog.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Jobs\\PersistAuditLog',
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'create',
        2 => 27,
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Foundation\\Queue\\Queueable',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/AuditLog.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\AuditLog',
        1 => 'casts',
        2 => 'App\\Models\\AuditLog',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/FeatureFlagOverride.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\FeatureFlagOverride',
        1 => 'casts',
        2 => 'App\\Models\\FeatureFlagOverride',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/IncomingWebhook.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\IncomingWebhook',
        1 => 'casts',
        2 => 'App\\Models\\IncomingWebhook',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/SocialAccount.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\SocialAccount',
        1 => 'casts',
        2 => 'App\\Models\\SocialAccount',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/User.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\User',
        1 => 'casts',
        2 => 'App\\Models\\User',
      ),
      1 => 
      array (
        0 => 'App\\Models\\User',
        1 => 'isAdmin',
        2 => 'App\\Models\\User',
      ),
      2 => 
      array (
        0 => 'App\\Models\\User',
        1 => 'hasPassword',
        2 => 'App\\Models\\User',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Laravel\\Cashier\\Billable',
        1 => 'Laravel\\Sanctum\\HasApiTokens',
        2 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        3 => 'Illuminate\\Notifications\\Notifiable',
        4 => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
        5 => 'Laragear\\TwoFactor\\TwoFactorAuthentication',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/UserSetting.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 80,
      ),
      1 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 90,
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/WebhookDelivery.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\WebhookDelivery',
        1 => 'casts',
        2 => 'App\\Models\\WebhookDelivery',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/WebhookEndpoint.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\WebhookEndpoint',
        1 => 'casts',
        2 => 'App\\Models\\WebhookEndpoint',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Notifications/IncompletePaymentReminder.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Notifications\\IncompletePaymentReminder',
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Bus\\Queueable',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Notifications/PaymentFailedNotification.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Notifications\\PaymentFailedNotification',
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Bus\\Queueable',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Notifications/RefundProcessedNotification.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Notifications\\RefundProcessedNotification',
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Bus\\Queueable',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Policies/UserPolicy.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Policies\\UserPolicy',
        1 => 'update',
        2 => 'App\\Policies\\UserPolicy',
      ),
      1 => 
      array (
        0 => 'App\\Policies\\UserPolicy',
        1 => 'delete',
        2 => 'App\\Policies\\UserPolicy',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Providers/AppServiceProvider.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Laravel\\Cashier\\Cashier',
        1 => 'ignoreRoutes',
        2 => 22,
      ),
      1 => 
      array (
        0 => 'Illuminate\\Support\\Facades\\Gate',
        1 => 'policy',
        2 => 30,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AdminBillingStatsService.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Services\\AdminBillingStatsService',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AuditService.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Jobs\\PersistAuditLog',
        1 => 'dispatch',
        2 => 52,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/FeatureFlagService.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'updateOrCreate',
        2 => 205,
      ),
      1 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'updateOrCreate',
        2 => 240,
      ),
      2 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 466,
      ),
      3 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 474,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/HealthCheckService.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Connection',
        1 => 'select',
        2 => 62,
      ),
      1 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'put',
        2 => 74,
      ),
      2 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 76,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/PlanLimitService.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Services\\PlanLimitService',
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Cache\\Repository',
        1 => 'forget',
        2 => 83,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SessionDataMigrationService.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Services\\SessionDataMigrationService',
        1 => 'hasSessionData',
        2 => 'App\\Services\\SessionDataMigrationService',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/WebhookService.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'insert',
        2 => 37,
      ),
      1 => 
      array (
        0 => 'App\\Jobs\\DispatchWebhookJob',
        1 => 'dispatch',
        2 => 43,
      ),
      2 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'insert',
        2 => 56,
      ),
      3 => 
      array (
        0 => 'App\\Jobs\\DispatchWebhookJob',
        1 => 'dispatch',
        2 => 67,
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Support/CsvExport.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Support\\CsvExport',
    ),
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Support\\CsvExport',
        1 => 'getFilename',
        2 => 'App\\Support\\CsvExport',
      ),
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/routes/api.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Support\\Facades\\Route',
        1 => 'get',
        2 => 59,
      ),
      1 => 
      array (
        0 => 'Illuminate\\Support\\Facades\\Route',
        1 => 'post',
        2 => 60,
      ),
      2 => 
      array (
        0 => 'Illuminate\\Support\\Facades\\Route',
        1 => 'delete',
        2 => 61,
      ),
      3 => 
      array (
        0 => 'Illuminate\\Support\\Facades\\Route',
        1 => 'get',
        2 => 67,
      ),
      4 => 
      array (
        0 => 'Illuminate\\Support\\Facades\\Route',
        1 => 'post',
        2 => 68,
      ),
      5 => 
      array (
        0 => 'Illuminate\\Support\\Facades\\Route',
        1 => 'get',
        2 => 69,
      ),
      6 => 
      array (
        0 => 'Illuminate\\Support\\Facades\\Route',
        1 => 'patch',
        2 => 70,
      ),
      7 => 
      array (
        0 => 'Illuminate\\Support\\Facades\\Route',
        1 => 'delete',
        2 => 71,
      ),
      8 => 
      array (
        0 => 'Illuminate\\Support\\Facades\\Route',
        1 => 'get',
        2 => 72,
      ),
      9 => 
      array (
        0 => 'Illuminate\\Support\\Facades\\Route',
        1 => 'post',
        2 => 73,
      ),
    ),
  ),
); },
	'dependencies' => array (
  '/Users/sood/dev/heatware/laravel-react-starter/app/Console/Commands/CheckIncompletePayments.php' => 
  array (
    'fileHash' => '1cdcd4956dcb19bdebd2bde6d54239babe7b166438bd70f76671ea391f89b976',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Console/Commands/PruneAuditLogs.php' => 
  array (
    'fileHash' => '009653d3b5f681188599d31491fc522b418d25d5665438f3daaeb8dcec5027f7',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Enums/AdminCacheKey.php' => 
  array (
    'fileHash' => 'e5d6256e93bd16ba765c69205c570675fc35b6e88d5fe654ef40dfd94d37fef7',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
      2 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminNotificationsController.php',
      3 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminSocialAuthController.php',
      4 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminTokensController.php',
      5 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminTwoFactorController.php',
      6 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
      7 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminWebhooksController.php',
      8 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/TokenController.php',
      9 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
      10 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/StripeWebhookController.php',
      11 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/SubscriptionController.php',
      12 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AdminBillingStatsService.php',
      13 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/FeatureFlagService.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Exceptions/ConcurrentOperationException.php' => 
  array (
    'fileHash' => 'e49c4104098ce4790afece90a130323a7f6b014162acc306b80cd2604cbd4893',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/SubscriptionController.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/BillingService.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Helpers/QueryHelper.php' => 
  array (
    'fileHash' => 'a3da14d780fb4f44efe7ecb636084db1727ffe61ea9b3ac8fad11db61620d73a',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminNotificationsController.php',
      2 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminWebhooksController.php',
      3 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AdminBillingStatsService.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Helpers/features.php' => 
  array (
    'fileHash' => '4f96adf291e751749891275788709d5abafff3200d5f9c597c154ec4638cfe6a',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/NotificationController.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
      2 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/SocialAuthController.php',
      3 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/TwoFactorChallengeController.php',
      4 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/TwoFactorController.php',
      5 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/WebhookPageController.php',
      6 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/EnsureOnboardingCompleted.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php' => 
  array (
    'fileHash' => '5affc7839b426b307b7cb40bdfb423a05b539326bfbca2639a88fa64711fcc69',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php' => 
  array (
    'fileHash' => '181638be4d6e10fbbbacdaf8d666ecbb33a3b500f1ea88dbca107de61ca2721e',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminConfigController.php' => 
  array (
    'fileHash' => '0c80fb6843b7e62b47d9994dc04b0fef37dfb60abfd43c86d714411e2cdce046',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php' => 
  array (
    'fileHash' => 'e9baacabee2b7922b009fd3f981e570ce41e19b4a96808e66e6ff1ea535f5db2',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminFeatureFlagController.php' => 
  array (
    'fileHash' => '66972e609abf38e4c1284fe467714b6233c29ff5eaf5b22c7da157c6b160549b',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminHealthController.php' => 
  array (
    'fileHash' => '8fa51513654a0607506b02cdf3ef2477c31b084aea09ba149ad7a51738e0467b',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminImpersonationController.php' => 
  array (
    'fileHash' => '899ace6565cdcb946bb19be918174b6930ededc4c6d06563dc69eb5b44dfdf3d',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminNotificationsController.php' => 
  array (
    'fileHash' => '1902aa1ec677596817fcbbc66d7a10edf02d47e8acb9de60d40abd18be2d574a',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminSocialAuthController.php' => 
  array (
    'fileHash' => '9d3aee4ddee460abfafd486e5770f503df19a1a068c2d0d55c3fe22188524d8c',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminSystemController.php' => 
  array (
    'fileHash' => 'd206717b18cba54bcf5817c28b9f9d443f292dce6777a1aff7c65d9650205df9',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminTokensController.php' => 
  array (
    'fileHash' => 'a0ac69131e7f631752929597b5abf13b9dd8b0e69f1e98eb5ab302a20f16875a',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminTwoFactorController.php' => 
  array (
    'fileHash' => '3b8c9db4ea6879d121ae1318f604f96e3b4d24a0fe24d6993401632692fabe2a',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php' => 
  array (
    'fileHash' => '59b29895e6302f58d33f02db3667d1ab06dcfb5aea4d69cc8d0f640defec5799',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminWebhooksController.php' => 
  array (
    'fileHash' => '749cc0a5f4c7885639258e60606b28d23013cd8a9f8d72399bd14c6cbe1a5faa',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/NotificationController.php' => 
  array (
    'fileHash' => '7281a992ea69d5ff006f0b033e90ff8e8eb1101ee1ff425fc71ff3b1f5256b10',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/api.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/TokenController.php' => 
  array (
    'fileHash' => '416e854383c029fdee2f20aeed5155c6af7543d07570af5c7476dddd3fd91b1d',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/api.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/UserSettingsController.php' => 
  array (
    'fileHash' => 'efe090cdee970381fab985f6a6baa5efc413f5c6b64f445e490252a043e6a731',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/api.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php' => 
  array (
    'fileHash' => '2c9fa22d954f159e7313ca9d1e83ef1d51a7eb923a011136f32db414a55c20e0',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/api.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/AuthenticatedSessionController.php' => 
  array (
    'fileHash' => '94d5a7f7b3a60249156c288ef03195baf8725a094e32e009114fbcafa6a28d25',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/auth.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/ConfirmablePasswordController.php' => 
  array (
    'fileHash' => '99285d8af325a5a8d00dbea3c5443277a8ce9050d5a7fa29afce2e310191ef17',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/auth.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/EmailVerificationNotificationController.php' => 
  array (
    'fileHash' => '24d5704b7534ad365800a33e7c537bf63467f1271c4bc801b351c8a05a77e5b9',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/auth.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/EmailVerificationPromptController.php' => 
  array (
    'fileHash' => 'ac015dc07e1aed52ae2cb95a98b2e3c37174bba4ff5df85963fece8bf3dc3dd2',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/auth.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/NewPasswordController.php' => 
  array (
    'fileHash' => 'd0bf4f929f40bf79995505d1cfa51427ea32baf9a14d7bca13d36ad376671456',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/auth.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/PasswordController.php' => 
  array (
    'fileHash' => 'fc15a5c4c7ffd6a20e2d435a21c367b1bc2327b5a403ec002630655516394a43',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/auth.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/PasswordResetLinkController.php' => 
  array (
    'fileHash' => '7ea1208810d8c7846816167e8e1fdf489d5d4b1f5354262ce665d29c9f123005',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/auth.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/RegisteredUserController.php' => 
  array (
    'fileHash' => 'e37b4a3d46fe5a2f697610a3f1d4726ce6af021187e887547d8c03793339e3b3',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/auth.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/SocialAuthController.php' => 
  array (
    'fileHash' => '8860008ce0392e1a78d2edaaf2802548f2b45f4121fbd3f7f31ea07a05360137',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/auth.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/TwoFactorChallengeController.php' => 
  array (
    'fileHash' => '842714446bd8bc7a4d03cd0816628e4819c38145702c1bd5372d62812fc08f9f',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/auth.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/VerifyEmailController.php' => 
  array (
    'fileHash' => '5f6080a239709740135d9138085ceb1857c5570a0fea35e62c976fc90929631e',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/auth.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php' => 
  array (
    'fileHash' => '70eb46f08421db854bc5cc9c414823bf0c8e247c73ff6c46826c82d37e4f7242',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/PricingController.php' => 
  array (
    'fileHash' => '9979d19734e27e7478a972a4462b1a2f6a8b097a5502bafd177f3dc8eb062793',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/StripeWebhookController.php' => 
  array (
    'fileHash' => 'e14038ca8b6711dda63b37f33a57b52f7c8de38f5bc50fbe06bb46207910c62a',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/SubscriptionController.php' => 
  array (
    'fileHash' => '48e93df34b05377b48d79ea9ebd4e2ec1e29c604c56eaa8d7425cd75cd981b4a',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ChartsController.php' => 
  array (
    'fileHash' => '6417c3181cc92d59461bcfe1595eeec95ad88664ef6806f95a24fd9b51b96892',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Controller.php' => 
  array (
    'fileHash' => 'e5ddafa07059bfc9f8310767b0fc04dd3b8a1f50bcec1fd693b19f5555697825',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
      2 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminConfigController.php',
      3 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
      4 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminFeatureFlagController.php',
      5 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminHealthController.php',
      6 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminImpersonationController.php',
      7 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminNotificationsController.php',
      8 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminSocialAuthController.php',
      9 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminSystemController.php',
      10 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminTokensController.php',
      11 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminTwoFactorController.php',
      12 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
      13 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminWebhooksController.php',
      14 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/NotificationController.php',
      15 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/TokenController.php',
      16 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/UserSettingsController.php',
      17 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
      18 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/AuthenticatedSessionController.php',
      19 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/ConfirmablePasswordController.php',
      20 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/EmailVerificationNotificationController.php',
      21 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/EmailVerificationPromptController.php',
      22 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/NewPasswordController.php',
      23 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/PasswordController.php',
      24 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/PasswordResetLinkController.php',
      25 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/RegisteredUserController.php',
      26 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/SocialAuthController.php',
      27 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/TwoFactorChallengeController.php',
      28 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/VerifyEmailController.php',
      29 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
      30 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/PricingController.php',
      31 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/SubscriptionController.php',
      32 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ChartsController.php',
      33 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/DashboardController.php',
      34 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ExportController.php',
      35 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/HealthCheckController.php',
      36 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/OnboardingController.php',
      37 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ProfileController.php',
      38 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/SeoController.php',
      39 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/ApiTokenPageController.php',
      40 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/TwoFactorController.php',
      41 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/WebhookPageController.php',
      42 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Webhook/IncomingWebhookController.php',
      43 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/WelcomeController.php',
      44 => '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php',
      45 => '/Users/sood/dev/heatware/laravel-react-starter/routes/api.php',
      46 => '/Users/sood/dev/heatware/laravel-react-starter/routes/auth.php',
      47 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/DashboardController.php' => 
  array (
    'fileHash' => '8070a156b6046ac943a4bf0b713b41397bcf86384efc9ace59404da262606cde',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ExportController.php' => 
  array (
    'fileHash' => 'ddf405de69f65ae881d97318036abbe072daa1e69bdcf147862fdb0c43c7e446',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/HealthCheckController.php' => 
  array (
    'fileHash' => '5714f6d7b287d149c02aec70b185b740a93658db8c2a96a4f0faccf6ce6f2eba',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/OnboardingController.php' => 
  array (
    'fileHash' => '70478c18a70194d02c8732bce07d4c6c15306fb42c8650b1bfc4472e8003456b',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ProfileController.php' => 
  array (
    'fileHash' => 'ce22667cd4922c2f17134ac60eae8b5e6be98a4576d03e0c2adbbdd002532ffc',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/SeoController.php' => 
  array (
    'fileHash' => 'b469bc38c6252853ed3599c53d97550b863c5c7375060f743404efaef56ddb64',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/ApiTokenPageController.php' => 
  array (
    'fileHash' => '50671a8278be31f57410a033178a49b75b46854b5ddb29acb79935efc8677098',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/TwoFactorController.php' => 
  array (
    'fileHash' => '2d723f9b4b1c0fad7d0abbfe8e5bfeca0a891519243e0dd6f1f9c93d7e855fe4',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/WebhookPageController.php' => 
  array (
    'fileHash' => '8055035975808395d2f2f74638002c4af59468b764a658db863efe200696e02c',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Webhook/IncomingWebhookController.php' => 
  array (
    'fileHash' => '6fca0f61d553bf8a595f0dae81fd8eeb00162e9b07203e584cb9c1f203de6089',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/api.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/WelcomeController.php' => 
  array (
    'fileHash' => '7119468d729cbdc90594c97376be2416ee290102604eacb9bc44faf90525958c',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/EnsureIsAdmin.php' => 
  array (
    'fileHash' => '2e39ce585f69f532d2dbf98ee012d048fdfa0a38066b8d5e7899b541983ec402',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/EnsureOnboardingCompleted.php' => 
  array (
    'fileHash' => '81262915a559a81f9f045c67251af6d0cb04f40341ad926b86e522f12bbf00f0',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/EnsureSubscribed.php' => 
  array (
    'fileHash' => '8c8c34d50640e36b95bb415788b0ae72dab289fcf9a703bca5f2926489b7e949',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/HandleInertiaRequests.php' => 
  array (
    'fileHash' => 'c121223416bca0d6acab7cada15d7a1fc9e46e87480e034fd5d950fe741bd2cd',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/RateLimitHeaders.php' => 
  array (
    'fileHash' => '6bdc87f934bc890f40d1f38d6c1881d7f2785557f9c9a616114fbaf1afece269',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/RequestIdMiddleware.php' => 
  array (
    'fileHash' => 'b847a357c0d5df2379c0cb7ddfef29d18726b0c3b518bc06deed3e9bd343b96f',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/SecurityHeaders.php' => 
  array (
    'fileHash' => 'bab3a879a0dc5b619c206cadab1b97e0f8569c76c404cfbb46f3b7079aa88588',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/VerifyWebhookSignature.php' => 
  array (
    'fileHash' => 'ae834f98b377eac4b17c03e5cb2245384e63a5213a0b82a888409173c4ff5f30',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminAuditLogIndexRequest.php' => 
  array (
    'fileHash' => '9214f0b61c92e9ad01897a09f2f0b551210b5d90f29385e003d15a50962a0b43',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminExportRequest.php' => 
  array (
    'fileHash' => '3b6ea475d2df1891c18ba6754877efee4bd49d63b47f9c77afb98dca3e1c07e1',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminFeatureFlagRequest.php' => 
  array (
    'fileHash' => 'efd75c504a9a0de396297be7e2c73818896618be1130796ae4d57b36101183db',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminFeatureFlagController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminFeatureFlagUserRequest.php' => 
  array (
    'fileHash' => '9dba7496ee53b10709dbd6cd7a60b5096737407414149e1ad4801a3c32553421',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminFeatureFlagController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminSubscriptionIndexRequest.php' => 
  array (
    'fileHash' => 'b78ec5b94f5a9e866bf5f57c57a749a2eedcdd4bd867f322754b22b2b597b5cb',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminUserIndexRequest.php' => 
  array (
    'fileHash' => 'd16ec5d45b7a3796e38f76543d3fb4718fe8ba4a26fabfca1500ae248bf45cbf',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Api/CreateTokenRequest.php' => 
  array (
    'fileHash' => '9527fed3555fa18ed65c5abaa6938434167edb39d0903d7913eb18fb6426802a',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/TokenController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Api/UpdateSettingRequest.php' => 
  array (
    'fileHash' => 'f55d9bb0128ed9e4eb414f00bb9a7a37608d2ad2056e2612b9d77c81bef495ed',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/UserSettingsController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Auth/LoginRequest.php' => 
  array (
    'fileHash' => 'b910724ab19ceb696881b7428be6b9372d869f2b959d0dca6f3cbcfef4e80e1d',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/AuthenticatedSessionController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Auth/PasswordUpdateRequest.php' => 
  array (
    'fileHash' => '1609ecc5175c8c8e2582794f89430c0ec7ffd62a1596dd37ab2ca1f8605e9746',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/PasswordController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Auth/RegisterRequest.php' => 
  array (
    'fileHash' => 'de269d9dc5149c38f1225efc40e7345e90cba436c3dfe77c2e6beaf64934865b',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/RegisteredUserController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/CancelSubscriptionRequest.php' => 
  array (
    'fileHash' => '4c559b0353dfb6af453f9c844948df45c64629724e5aac30f2db175b3c333407',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/SubscriptionController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/Concerns/HasPriceValidation.php' => 
  array (
    'fileHash' => '41f389983d4856f6bc326d28b91bc96a6e43c6e0fcb7a0d79a03137cd5b552c6',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/SubscriptionController.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/SubscribeRequest.php',
      2 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/SwapPlanRequest.php',
    ),
    'usedTraitDependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/SubscribeRequest.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/SwapPlanRequest.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/SubscribeRequest.php' => 
  array (
    'fileHash' => 'd4263f8bcbe65d05a0f2cd9787b98ebd3f47be4a86f00c750777dfe804db93ac',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/SubscriptionController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/SwapPlanRequest.php' => 
  array (
    'fileHash' => '7b690b1b6798e13924bd05c9c6509cb5226a86b70fc216d8cf6311c89ddbb037',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/SubscriptionController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/UpdatePaymentMethodRequest.php' => 
  array (
    'fileHash' => 'cf6cf56eea28890a5e12fe0b5a4404faa4a8835f5a7640b4267cbd85cbd14eba',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/SubscriptionController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/UpdateQuantityRequest.php' => 
  array (
    'fileHash' => 'fe2b016e9f3aaaf9e7eb626e4d8941400049ebd42a079400281afc20128dc0e3',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/SubscriptionController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/DeleteAccountRequest.php' => 
  array (
    'fileHash' => '8cd5bc1b7575dca40402df804133dfe5e33e32433577389d612074e2416134df',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ProfileController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/ExportRequest.php' => 
  array (
    'fileHash' => 'f51f83cfc123f1417595b7687be82eb164104cba9150d882aea7a257981e2df8',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ExportController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/FileUploadRequest.php' => 
  array (
    'fileHash' => '76ad4315a69d2394c93ce6d69e0dd46eb53d1697970dbf4b3ee5530a63dfe9c0',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/ProfileUpdateRequest.php' => 
  array (
    'fileHash' => 'f3abbb394b369884549e57ad1a999c81256ab7fc80879d27e67f84dc161a6ca8',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ProfileController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/TwoFactor/ConfirmTwoFactorRequest.php' => 
  array (
    'fileHash' => 'cc4f7ac738889129f3e893955e5943b7d3111215c2b5e6a32d173dd5ab669da4',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/TwoFactorController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/TwoFactor/DisableTwoFactorRequest.php' => 
  array (
    'fileHash' => 'b84ea75d89334504dc100cacabedda7fd71484f794b7a83aafef12f438a96e09',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/TwoFactorController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/TwoFactor/TwoFactorChallengeRequest.php' => 
  array (
    'fileHash' => '31300304c58696567de98fcfebb9cad412c152e3c892e74f954ff8ff3f6838af',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/TwoFactorChallengeController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Webhook/CreateWebhookEndpointRequest.php' => 
  array (
    'fileHash' => '1312ba6339f116e8a0409071dd773b4b3a72e4b2da9e0ea1914b079f735bf130',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Webhook/UpdateWebhookEndpointRequest.php' => 
  array (
    'fileHash' => 'b6c1a5939bcd05a33ccb129ddaa5b8e0ec1f125fcdebeed8780281dd3e3daea6',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/CancelOrphanedStripeSubscription.php' => 
  array (
    'fileHash' => 'ab3b436fb07305f3508083ea8b215dd7480865ded01c2e37c46a0210bf9670b8',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ProfileController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/DispatchWebhookJob.php' => 
  array (
    'fileHash' => 'c0a86eb8a6909e480e6eccf2ee26fa2cf15655c189f31edada2219c34a9c5e96',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/WebhookService.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/PersistAuditLog.php' => 
  array (
    'fileHash' => '845e58d818452fe26aeb3ead75e572daef0354408de67bc61d2d8adfdb8c0363',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AuditService.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Listeners/SendEmailVerificationNotification.php' => 
  array (
    'fileHash' => 'e32421646a6529824938142c21d64f0c6cabcb9ba77953d2e14dd31aa383056a',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Providers/EventServiceProvider.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/AuditLog.php' => 
  array (
    'fileHash' => '05b074a4337aa78689339bc2c13b187fffada876ba41318c8ee1e5397330ea86',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Console/Commands/PruneAuditLogs.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php',
      2 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
      3 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
      4 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
      5 => '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/PersistAuditLog.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/FeatureFlagOverride.php' => 
  array (
    'fileHash' => '8fbd39d2c4e79ef6bc8a1583d07880e5905bdcd8413cf9fb47753d13970ad8dc',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Enums/AdminCacheKey.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/FeatureFlagService.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/IncomingWebhook.php' => 
  array (
    'fileHash' => 'a77de0a95cb4a50d73e5f710edc63123e6f4d189dd290ada8444c8b6981663f0',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Webhook/IncomingWebhookController.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/IncomingWebhookService.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/SocialAccount.php' => 
  array (
    'fileHash' => '7be087e21b090e8861bcd8b6af2725e3c4c7023b65b7c9338194e6c1ac195bc1',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/SocialAuthController.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Models/User.php',
      2 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/User.php' => 
  array (
    'fileHash' => '022ff4406657c18d133fe480f58a186e241e6c76e1d1c927d6946e6900b1c5a1',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Helpers/features.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php',
      2 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminFeatureFlagController.php',
      3 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminImpersonationController.php',
      4 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminTwoFactorController.php',
      5 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
      6 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/NotificationController.php',
      7 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/TokenController.php',
      8 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/UserSettingsController.php',
      9 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
      10 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/AuthenticatedSessionController.php',
      11 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/ConfirmablePasswordController.php',
      12 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/EmailVerificationNotificationController.php',
      13 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/EmailVerificationPromptController.php',
      14 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/PasswordController.php',
      15 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/RegisteredUserController.php',
      16 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/SocialAuthController.php',
      17 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/TwoFactorChallengeController.php',
      18 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/VerifyEmailController.php',
      19 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
      20 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/PricingController.php',
      21 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/StripeWebhookController.php',
      22 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/SubscriptionController.php',
      23 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ExportController.php',
      24 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ProfileController.php',
      25 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/TwoFactorController.php',
      26 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/WebhookPageController.php',
      27 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/EnsureIsAdmin.php',
      28 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/EnsureOnboardingCompleted.php',
      29 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/EnsureSubscribed.php',
      30 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/HandleInertiaRequests.php',
      31 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminAuditLogIndexRequest.php',
      32 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminExportRequest.php',
      33 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminFeatureFlagRequest.php',
      34 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminFeatureFlagUserRequest.php',
      35 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminSubscriptionIndexRequest.php',
      36 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminUserIndexRequest.php',
      37 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Auth/LoginRequest.php',
      38 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Auth/RegisterRequest.php',
      39 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/ProfileUpdateRequest.php',
      40 => '/Users/sood/dev/heatware/laravel-react-starter/app/Models/AuditLog.php',
      41 => '/Users/sood/dev/heatware/laravel-react-starter/app/Models/FeatureFlagOverride.php',
      42 => '/Users/sood/dev/heatware/laravel-react-starter/app/Models/SocialAccount.php',
      43 => '/Users/sood/dev/heatware/laravel-react-starter/app/Models/UserSetting.php',
      44 => '/Users/sood/dev/heatware/laravel-react-starter/app/Models/WebhookEndpoint.php',
      45 => '/Users/sood/dev/heatware/laravel-react-starter/app/Policies/UserPolicy.php',
      46 => '/Users/sood/dev/heatware/laravel-react-starter/app/Providers/AppServiceProvider.php',
      47 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AuditService.php',
      48 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/BillingService.php',
      49 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/FeatureFlagService.php',
      50 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/PlanLimitService.php',
      51 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SessionDataMigrationService.php',
      52 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php',
      53 => '/Users/sood/dev/heatware/laravel-react-starter/config/auth.php',
      54 => '/Users/sood/dev/heatware/laravel-react-starter/routes/api.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/UserSetting.php' => 
  array (
    'fileHash' => '9bebb1eb782882fded691d8e05114eeb1dd88528c3598b6cbe8adce8955c25a8',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Models/User.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/WebhookDelivery.php' => 
  array (
    'fileHash' => 'ec462afb5a78fb24e53dac6dc3895af9d257f139c4acf5adcbf927fffcb6b701',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/DispatchWebhookJob.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Models/WebhookEndpoint.php',
      2 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/WebhookService.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/WebhookEndpoint.php' => 
  array (
    'fileHash' => 'f71677fa297e3561ae2e0fba191543c4a004cbca592fdba6d7b289a827030004',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Models/User.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Models/WebhookDelivery.php',
      2 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/WebhookService.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Notifications/IncompletePaymentReminder.php' => 
  array (
    'fileHash' => '212e2b3a2cc70afd6c5268007e9e4dce8928c4ded4311736236c77751444c7cb',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Console/Commands/CheckIncompletePayments.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Notifications/PaymentFailedNotification.php' => 
  array (
    'fileHash' => 'a52ce5a5fd710fb144f20406c9bb8769e248d58b2784cd0d508d3a7a7309c48e',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/StripeWebhookController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Notifications/RefundProcessedNotification.php' => 
  array (
    'fileHash' => '488082f9b33f00ebd526daf280d0526afa48b75cd663b687e272ed817032a72d',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/StripeWebhookController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Policies/UserPolicy.php' => 
  array (
    'fileHash' => 'a808bd4014368f1a113468f5923a19cc3938caa0a7b6759612c02adef34d6bfd',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Providers/AppServiceProvider.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Providers/AppServiceProvider.php' => 
  array (
    'fileHash' => '32a0dfaa8308146efd89e0cb3072afce02d2af19843bb4821746915d60cb061c',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Providers/EventServiceProvider.php' => 
  array (
    'fileHash' => '725eb3e6355a28357967332bae58bf37e9a822c096a46c87cb6d53412f90df11',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AdminBillingStatsService.php' => 
  array (
    'fileHash' => 'd172729abfb5bc5db99d41fa345066153d7a8d2f3ec240d25ea3e60d3bff97a4',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AuditService.php' => 
  array (
    'fileHash' => '72264b1eab8b826986ca84be75a4fd54cfb965e3c45fcc2a01e62b40c9321c02',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminFeatureFlagController.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminImpersonationController.php',
      2 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php',
      3 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/AuthenticatedSessionController.php',
      4 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/RegisteredUserController.php',
      5 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/TwoFactorChallengeController.php',
      6 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/StripeWebhookController.php',
      7 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/SubscriptionController.php',
      8 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ProfileController.php',
      9 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/TwoFactorController.php',
      10 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/EnsureIsAdmin.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/BillingService.php' => 
  array (
    'fileHash' => '59a95717e8d27181322e2fa56578df02ce95279ee4ebe5bbc2296db6f24aa7ab',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
      2 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/SubscriptionController.php',
      3 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ProfileController.php',
      4 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/EnsureSubscribed.php',
      5 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/HandleInertiaRequests.php',
      6 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AdminBillingStatsService.php',
      7 => '/Users/sood/dev/heatware/laravel-react-starter/app/Services/PlanLimitService.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/FeatureFlagService.php' => 
  array (
    'fileHash' => '9945aaec7078db8d336858500581ae11545af4b92eda637270f5b14279fc2cf6',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Helpers/features.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminFeatureFlagController.php',
      2 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/HandleInertiaRequests.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/HealthCheckService.php' => 
  array (
    'fileHash' => 'c6a375c8df6197b0ecf8a21599ab464149b1eca8093792bab6eb40d4343fc759',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminHealthController.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/HealthCheckController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/IncomingWebhookService.php' => 
  array (
    'fileHash' => 'fc0d3e2b8bd71ac434eaf156861a86f17edda030bf746687801e3d697b8b9cab',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Webhook/IncomingWebhookController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/PlanLimitService.php' => 
  array (
    'fileHash' => '472081364585f9195ab29e702ca2159910ddbfe0da0e8c1237fd21b779aa1f19',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/RegisteredUserController.php',
      2 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php',
      3 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/PricingController.php',
      4 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/StripeWebhookController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SessionDataMigrationService.php' => 
  array (
    'fileHash' => 'd73ac58ad312c10d48e143ae54c2754e88142554fc046b2b52298eb6c63f822d',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/RegisteredUserController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php' => 
  array (
    'fileHash' => 'a645ffc00dad2f214578f575380fdfb5e75dd850f045632efeae7f33773b5693',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/SocialAuthController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/WebhookService.php' => 
  array (
    'fileHash' => '96ac793012d4994836dc711d98b1815746d993b1b91923c203ae2eecdbd37f8b',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php',
      1 => '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/DispatchWebhookJob.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Support/CsvExport.php' => 
  array (
    'fileHash' => '14a7be6cafa4284a5dbf87d4a4438c2d17ab00820012eec481f7e1b82fbdcdaf',
    'dependentFiles' => 
    array (
      0 => '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ExportController.php',
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/app.php' => 
  array (
    'fileHash' => '7bd46be22bbbe85efcc3b9cbacb8dc0b05b5d0a86b0ceffe342887d70f5edde4',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/auth.php' => 
  array (
    'fileHash' => 'd1954a62c8299924af7e281578269d22b21b84760e83a4834de86ade88daf341',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/cache.php' => 
  array (
    'fileHash' => '408dbe6f1ae6d4860526d3b55f378052eedf1805e76be80623b9e07ccf1e932d',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/cashier.php' => 
  array (
    'fileHash' => 'ccd05c33c02ec602890b29b5c35960b519ce288c6dc12b61c7e28f4a6709a99f',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/cors.php' => 
  array (
    'fileHash' => '4ed97c8dcef389dfa48990503bff2e38c12bcef82a158c8024a161a554a1f0c6',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/database.php' => 
  array (
    'fileHash' => '94f0e3a2eb0d8b999e427aa551d830fdef5a926afe050d4432abdb9064073b57',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/features.php' => 
  array (
    'fileHash' => '30b1d2f662740be3e50c01cf9390179266c848f6df0b13fb42323e4713fdde7d',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/filesystems.php' => 
  array (
    'fileHash' => '2e841c25e7419b9f66796117c902d4b642468886f2492ae2faf50ee715420d58',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/health.php' => 
  array (
    'fileHash' => 'd536136bd4db534ee065f1d4988b8d4d440e26328751891ce4b4f91a5d83d7a7',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/logging.php' => 
  array (
    'fileHash' => '50422aae7358371948f66f7fa4312bba65886f103a435e914c939a2e25c12183',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/mail.php' => 
  array (
    'fileHash' => '715979a55159891f584998329ff697f38335a28db368c6c74709a94026b621f0',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/plans.php' => 
  array (
    'fileHash' => '5903b0d55bd9b83a559a0a83c28c2872168c5dd155ba4009c03117b7e6b754d1',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/queue.php' => 
  array (
    'fileHash' => '5290c979097dafab403a0644d13b51827076ab0f06d54eefc1ce8a5aea103482',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/sanctum.php' => 
  array (
    'fileHash' => '0e55e160a49d1bc4b041f3291a08631fddeea35e89f2d1109304d430f21add50',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/scribe.php' => 
  array (
    'fileHash' => '8da6c987a9326ad4195357f48a5533ac1e4556609757704325a408abc1e8e31a',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/security.php' => 
  array (
    'fileHash' => '0ab5377d296eb7667c778b64e4f40eb61141bb493759502e6611780183a01319',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/sentry.php' => 
  array (
    'fileHash' => '5842f3742054d70194701ef4e4f358710f1631ac26ead1f6287e7922faf2c68d',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/services.php' => 
  array (
    'fileHash' => '7d87df9174685eaa4c01b9776772eda54e27f7ac8bac884221b02ef7e895b961',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/session.php' => 
  array (
    'fileHash' => 'c793b2d9d99891cfdd0b6db1ae313e75ea6dea9767d9a07d8b365ec1464a05a5',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/two-factor.php' => 
  array (
    'fileHash' => 'b2bd35b62028a6d88f58ae76813d2ce9abf6e262178113625489925415cb7959',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/config/webhooks.php' => 
  array (
    'fileHash' => '29db074e7eb2ac80e677d0cf0f761a25211d1ce7ac3255642605c969e85260f0',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/routes/admin.php' => 
  array (
    'fileHash' => 'de5b881d92ea20695d9f67278e39c9b7072124d191e3738e603b11364be83139',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/routes/api.php' => 
  array (
    'fileHash' => '29e3454d1c448082dee38fb7fde5f169f32990d0c9c5c1d39d1c11a72a1514d2',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/routes/auth.php' => 
  array (
    'fileHash' => '8d2ceff0d5454cbdeba8cf82d18a5e9a1ebc0c8e91a4a1ee3cbce83073aea2b0',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/routes/console.php' => 
  array (
    'fileHash' => '702eabe02313b7203a7f5fe56d9b405d27b0ddaa007cd9fa6db019b39c10ab8a',
    'dependentFiles' => 
    array (
    ),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/routes/web.php' => 
  array (
    'fileHash' => 'abadd11229482e3ce2dbc2b38a3b7263e7b1ce0414ef70c134ac5d3d30fff841',
    'dependentFiles' => 
    array (
    ),
  ),
),
	'exportedNodesCallback' => static function (): array { return array (
  '/Users/sood/dev/heatware/laravel-react-starter/app/Console/Commands/CheckIncompletePayments.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Console\\Commands\\CheckIncompletePayments',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Console\\Command',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'signature',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'description',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Console/Commands/PruneAuditLogs.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Console\\Commands\\PruneAuditLogs',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Console\\Command',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'signature',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'description',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Enums/AdminCacheKey.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedEnumNode::__set_state(array(
       'name' => 'App\\Enums\\AdminCacheKey',
       'scalarType' => 'string',
       'phpDoc' => NULL,
       'implements' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'DASHBOARD_STATS',
           'value' => '\'admin:dashboard:stats\'',
           'phpDoc' => NULL,
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'DASHBOARD_SIGNUP_CHART',
           'value' => '\'admin:dashboard:signup_chart\'',
           'phpDoc' => NULL,
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'BILLING_STATS',
           'value' => '\'admin:billing:stats\'',
           'phpDoc' => NULL,
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'BILLING_TIER_DIST',
           'value' => '\'admin:billing:tier_dist\'',
           'phpDoc' => NULL,
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'BILLING_STATUS',
           'value' => '\'admin:billing:status\'',
           'phpDoc' => NULL,
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'BILLING_GROWTH_CHART',
           'value' => '\'admin:billing:growth_chart\'',
           'phpDoc' => NULL,
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'BILLING_TRIALS',
           'value' => '\'admin:billing:trials\'',
           'phpDoc' => NULL,
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'AUDIT_EVENT_TYPES',
           'value' => '\'admin:audit_logs:event_types\'',
           'phpDoc' => NULL,
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'SOCIAL_AUTH_STATS',
           'value' => '\'admin:social_auth:stats\'',
           'phpDoc' => NULL,
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'WEBHOOKS_STATS',
           'value' => '\'admin:webhooks:stats\'',
           'phpDoc' => NULL,
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'WEBHOOKS_DELIVERY_CHART',
           'value' => '\'admin:webhooks:delivery_chart\'',
           'phpDoc' => NULL,
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'WEBHOOKS_RECENT_FAILURES',
           'value' => '\'admin:webhooks:recent_failures\'',
           'phpDoc' => NULL,
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'TOKENS_STATS',
           'value' => '\'admin:tokens:stats\'',
           'phpDoc' => NULL,
        )),
        13 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'TOKENS_MOST_ACTIVE',
           'value' => '\'admin:tokens:most_active\'',
           'phpDoc' => NULL,
        )),
        14 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'TWO_FACTOR_STATS',
           'value' => '\'admin:two_factor:stats\'',
           'phpDoc' => NULL,
        )),
        15 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'NOTIFICATIONS_STATS',
           'value' => '\'admin:notifications:stats\'',
           'phpDoc' => NULL,
        )),
        16 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'NOTIFICATIONS_VOLUME',
           'value' => '\'admin:notifications:volume\'',
           'phpDoc' => NULL,
        )),
        17 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'FEATURE_FLAGS_GLOBAL',
           'value' => '\'admin:feature_flags:global\'',
           'phpDoc' => NULL,
        )),
        18 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'DEFAULT_TTL',
               'value' => '300',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/** Default TTL in seconds for most admin caches. */',
             'namespace' => 'App\\Enums',
             'uses' => 
            array (
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
        )),
        19 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'CHART_TTL',
               'value' => '3600',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/** Longer TTL for chart data that changes infrequently. */',
             'namespace' => 'App\\Enums',
             'uses' => 
            array (
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
        )),
        20 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'flushAll',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Flush all admin caches.
     */',
             'namespace' => 'App\\Enums',
             'uses' => 
            array (
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        21 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'featureFlagsUser',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the cache key for user-specific feature flag overrides.
     */',
             'namespace' => 'App\\Enums',
             'uses' => 
            array (
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Exceptions/ConcurrentOperationException.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Exceptions\\ConcurrentOperationException',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'RuntimeException',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Helpers/QueryHelper.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Helpers\\QueryHelper',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'dateExpression',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get a database-aware DATE() expression for grouping by day.
     * Works with both MySQL (DATE()) and SQLite (date()).
     *
     * @param  string  $column  Must be a valid column identifier (letters, digits, underscores, dots only).
     */',
             'namespace' => 'App\\Helpers',
             'uses' => 
            array (
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'Illuminate\\Database\\Query\\Expression',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'column',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Helpers/features.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'feature_enabled',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
     * Check if a feature flag is enabled for the given user.
     *
     * Resolution order:
     * 1. User-specific override (if user provided)
     * 2. Global database override
     * 3. Config default from features.php
     *
     * @param  string  $flag  The feature flag key (e.g., \'billing\', \'two_factor\')
     * @param  User|null  $user  The user to check for (defaults to authenticated user)
     */',
         'namespace' => NULL,
         'uses' => 
        array (
          'user' => 'App\\Models\\User',
          'featureflagservice' => 'App\\Services\\FeatureFlagService',
        ),
         'constUses' => 
        array (
        ),
      )),
       'byRef' => false,
       'returnType' => 'bool',
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'flag',
           'type' => 'string',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'user',
           'type' => '?App\\Models\\User',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => true,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminAuditLogController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Admin\\AdminAuditLogController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'index',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Admin\\AdminAuditLogIndexRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'show',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'auditLog',
               'type' => 'App\\Models\\AuditLog',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'export',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Admin\\AdminExportRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminBillingController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Admin\\AdminBillingController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'billingService',
               'type' => 'App\\Services\\BillingService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'statsService',
               'type' => 'App\\Services\\AdminBillingStatsService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'dashboard',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'subscriptions',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Admin\\AdminSubscriptionIndexRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'show',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'subscription',
               'type' => 'Laravel\\Cashier\\Subscription',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminConfigController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Admin\\AdminConfigController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminDashboardController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Admin\\AdminDashboardController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminFeatureFlagController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Admin\\AdminFeatureFlagController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'featureFlagService',
               'type' => 'App\\Services\\FeatureFlagService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'auditService',
               'type' => 'App\\Services\\AuditService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'index',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Display the feature flags index page.
     */',
             'namespace' => 'App\\Http\\Controllers\\Admin',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'adminfeatureflagrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagRequest',
              'adminfeatureflaguserrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagUserRequest',
              'auditservice' => 'App\\Services\\AuditService',
              'featureflagservice' => 'App\\Services\\FeatureFlagService',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'updateGlobal',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update a global feature flag override.
     */',
             'namespace' => 'App\\Http\\Controllers\\Admin',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'adminfeatureflagrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagRequest',
              'adminfeatureflaguserrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagUserRequest',
              'auditservice' => 'App\\Services\\AuditService',
              'featureflagservice' => 'App\\Services\\FeatureFlagService',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'flag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'removeGlobal',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Remove a global feature flag override.
     */',
             'namespace' => 'App\\Http\\Controllers\\Admin',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'adminfeatureflagrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagRequest',
              'adminfeatureflaguserrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagUserRequest',
              'auditservice' => 'App\\Services\\AuditService',
              'featureflagservice' => 'App\\Services\\FeatureFlagService',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'flag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getTargetedUsers',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get users with overrides for a flag.
     */',
             'namespace' => 'App\\Http\\Controllers\\Admin',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'adminfeatureflagrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagRequest',
              'adminfeatureflaguserrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagUserRequest',
              'auditservice' => 'App\\Services\\AuditService',
              'featureflagservice' => 'App\\Services\\FeatureFlagService',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'flag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'addUserOverride',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Add a user-specific override.
     */',
             'namespace' => 'App\\Http\\Controllers\\Admin',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'adminfeatureflagrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagRequest',
              'adminfeatureflaguserrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagUserRequest',
              'auditservice' => 'App\\Services\\AuditService',
              'featureflagservice' => 'App\\Services\\FeatureFlagService',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagUserRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'flag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'removeUserOverride',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Remove a user-specific override.
     */',
             'namespace' => 'App\\Http\\Controllers\\Admin',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'adminfeatureflagrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagRequest',
              'adminfeatureflaguserrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagUserRequest',
              'auditservice' => 'App\\Services\\AuditService',
              'featureflagservice' => 'App\\Services\\FeatureFlagService',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'flag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'removeAllUserOverrides',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Remove all user overrides for a flag.
     */',
             'namespace' => 'App\\Http\\Controllers\\Admin',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'adminfeatureflagrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagRequest',
              'adminfeatureflaguserrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagUserRequest',
              'auditservice' => 'App\\Services\\AuditService',
              'featureflagservice' => 'App\\Services\\FeatureFlagService',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'flag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'searchUsers',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Search users for targeting.
     */',
             'namespace' => 'App\\Http\\Controllers\\Admin',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'adminfeatureflagrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagRequest',
              'adminfeatureflaguserrequest' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagUserRequest',
              'auditservice' => 'App\\Services\\AuditService',
              'featureflagservice' => 'App\\Services\\FeatureFlagService',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminHealthController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Admin\\AdminHealthController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'healthCheckService',
               'type' => 'App\\Services\\HealthCheckService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminImpersonationController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Admin\\AdminImpersonationController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'auditService',
               'type' => 'App\\Services\\AuditService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'start',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'stop',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminNotificationsController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Admin\\AdminNotificationsController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminSocialAuthController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Admin\\AdminSocialAuthController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminSystemController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Admin\\AdminSystemController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminTokensController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Admin\\AdminTokensController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminTwoFactorController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Admin\\AdminTwoFactorController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminUsersController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Admin\\AdminUsersController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'auditService',
               'type' => 'App\\Services\\AuditService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'index',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Admin\\AdminUserIndexRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'show',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'toggleAdmin',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'bulkDeactivate',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'toggleActive',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Admin/AdminWebhooksController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Admin\\AdminWebhooksController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/NotificationController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Api\\NotificationController',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * @group Notifications
 *
 * Manage in-app notifications.
 */',
         'namespace' => 'App\\Http\\Controllers\\Api',
         'uses' => 
        array (
          'controller' => 'App\\Http\\Controllers\\Controller',
          'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
          'request' => 'Illuminate\\Http\\Request',
          'cache' => 'Illuminate\\Support\\Facades\\Cache',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'index',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * List notifications
     *
     * @authenticated
     */',
             'namespace' => 'App\\Http\\Controllers\\Api',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'request' => 'Illuminate\\Http\\Request',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'markAsRead',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Mark as read
     *
     * @authenticated
     *
     * @urlParam id string required The notification ID. Example: 550e8400-e29b-41d4-a716-446655440000
     */',
             'namespace' => 'App\\Http\\Controllers\\Api',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'request' => 'Illuminate\\Http\\Request',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'id',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'markAllAsRead',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Mark all as read
     *
     * @authenticated
     */',
             'namespace' => 'App\\Http\\Controllers\\Api',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'request' => 'Illuminate\\Http\\Request',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'destroy',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Delete notification
     *
     * @authenticated
     *
     * @urlParam id string required The notification ID. Example: 550e8400-e29b-41d4-a716-446655440000
     */',
             'namespace' => 'App\\Http\\Controllers\\Api',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'request' => 'Illuminate\\Http\\Request',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'id',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/TokenController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Api\\TokenController',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * @group API Tokens
 *
 * Manage personal access tokens for API authentication.
 */',
         'namespace' => 'App\\Http\\Controllers\\Api',
         'uses' => 
        array (
          'admincachekey' => 'App\\Enums\\AdminCacheKey',
          'controller' => 'App\\Http\\Controllers\\Controller',
          'createtokenrequest' => 'App\\Http\\Requests\\Api\\CreateTokenRequest',
          'datetimeimmutable' => 'DateTimeImmutable',
          'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
          'request' => 'Illuminate\\Http\\Request',
          'cache' => 'Illuminate\\Support\\Facades\\Cache',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'index',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * List tokens
     *
     * Get all API tokens for the authenticated user.
     *
     * @authenticated
     *
     * @response 200 [{"id":1,"name":"My Token","abilities":["*"],"last_used_at":null,"expires_at":null,"created_at":"2026-01-01T00:00:00.000000Z"}]
     */',
             'namespace' => 'App\\Http\\Controllers\\Api',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'controller' => 'App\\Http\\Controllers\\Controller',
              'createtokenrequest' => 'App\\Http\\Requests\\Api\\CreateTokenRequest',
              'datetimeimmutable' => 'DateTimeImmutable',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'request' => 'Illuminate\\Http\\Request',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'store',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create token
     *
     * Create a new personal access token.
     *
     * @authenticated
     *
     * @response 200 {"token":"1|abc123...","id":1}
     */',
             'namespace' => 'App\\Http\\Controllers\\Api',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'controller' => 'App\\Http\\Controllers\\Controller',
              'createtokenrequest' => 'App\\Http\\Requests\\Api\\CreateTokenRequest',
              'datetimeimmutable' => 'DateTimeImmutable',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'request' => 'Illuminate\\Http\\Request',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Api\\CreateTokenRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'destroy',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Delete token
     *
     * Revoke and delete a personal access token.
     *
     * @authenticated
     *
     * @urlParam tokenId integer required The token ID. Example: 1
     *
     * @response 200 {"success":true}
     * @response 404 {"message":"Token not found."}
     */',
             'namespace' => 'App\\Http\\Controllers\\Api',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'controller' => 'App\\Http\\Controllers\\Controller',
              'createtokenrequest' => 'App\\Http\\Requests\\Api\\CreateTokenRequest',
              'datetimeimmutable' => 'DateTimeImmutable',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'request' => 'Illuminate\\Http\\Request',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'tokenId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/UserSettingsController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Api\\UserSettingsController',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * @group User Settings
 *
 * Manage user preferences such as theme and timezone.
 */',
         'namespace' => 'App\\Http\\Controllers\\Api',
         'uses' => 
        array (
          'controller' => 'App\\Http\\Controllers\\Controller',
          'updatesettingrequest' => 'App\\Http\\Requests\\Api\\UpdateSettingRequest',
          'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
          'request' => 'Illuminate\\Http\\Request',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'index',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get settings
     *
     * Retrieve all settings for the authenticated user.
     *
     * @authenticated
     *
     * @response 200 {"theme":"system","timezone":"UTC"}
     */',
             'namespace' => 'App\\Http\\Controllers\\Api',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'updatesettingrequest' => 'App\\Http\\Requests\\Api\\UpdateSettingRequest',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'request' => 'Illuminate\\Http\\Request',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'store',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update setting
     *
     * Create or update a user setting.
     *
     * @authenticated
     *
     * @response 200 {"success":true}
     */',
             'namespace' => 'App\\Http\\Controllers\\Api',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'updatesettingrequest' => 'App\\Http\\Requests\\Api\\UpdateSettingRequest',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'request' => 'Illuminate\\Http\\Request',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Api\\UpdateSettingRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Api/WebhookEndpointController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Api\\WebhookEndpointController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'webhookService',
               'type' => 'App\\Services\\WebhookService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'planLimitService',
               'type' => 'App\\Services\\PlanLimitService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'index',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'store',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Webhook\\CreateWebhookEndpointRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'show',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'endpointId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Webhook\\UpdateWebhookEndpointRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'endpointId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'destroy',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'endpointId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'deliveries',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'endpointId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'test',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'endpointId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/AuthenticatedSessionController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Auth\\AuthenticatedSessionController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'auditService',
               'type' => 'App\\Services\\AuditService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'create',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Display the login view.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'loginrequest' => 'App\\Http\\Requests\\Auth\\LoginRequest',
              'auditservice' => 'App\\Services\\AuditService',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'route' => 'Illuminate\\Support\\Facades\\Route',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response|Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'store',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Handle an incoming authentication request.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'loginrequest' => 'App\\Http\\Requests\\Auth\\LoginRequest',
              'auditservice' => 'App\\Services\\AuditService',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'route' => 'Illuminate\\Support\\Facades\\Route',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Auth\\LoginRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'destroy',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Destroy an authenticated session.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'loginrequest' => 'App\\Http\\Requests\\Auth\\LoginRequest',
              'auditservice' => 'App\\Services\\AuditService',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'route' => 'Illuminate\\Support\\Facades\\Route',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/ConfirmablePasswordController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Auth\\ConfirmablePasswordController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'show',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Show the confirm password view.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'validationexception' => 'Illuminate\\Validation\\ValidationException',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'store',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Confirm the user\'s password.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'validationexception' => 'Illuminate\\Validation\\ValidationException',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/EmailVerificationNotificationController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Auth\\EmailVerificationNotificationController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'store',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Send a new email verification notification.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/EmailVerificationPromptController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Auth\\EmailVerificationPromptController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Display the email verification prompt.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse|Inertia\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/NewPasswordController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Auth\\NewPasswordController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'create',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Display the password reset view.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'passwordreset' => 'Illuminate\\Auth\\Events\\PasswordReset',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'hash' => 'Illuminate\\Support\\Facades\\Hash',
              'password' => 'Illuminate\\Support\\Facades\\Password',
              'str' => 'Illuminate\\Support\\Str',
              'rules' => 'Illuminate\\Validation\\Rules',
              'validationexception' => 'Illuminate\\Validation\\ValidationException',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'store',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Handle an incoming new password request.
     *
     * @throws \\Illuminate\\Validation\\ValidationException
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'passwordreset' => 'Illuminate\\Auth\\Events\\PasswordReset',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'hash' => 'Illuminate\\Support\\Facades\\Hash',
              'password' => 'Illuminate\\Support\\Facades\\Password',
              'str' => 'Illuminate\\Support\\Str',
              'rules' => 'Illuminate\\Validation\\Rules',
              'validationexception' => 'Illuminate\\Validation\\ValidationException',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/PasswordController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Auth\\PasswordController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update the user\'s password.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'passwordupdaterequest' => 'App\\Http\\Requests\\Auth\\PasswordUpdateRequest',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'hash' => 'Illuminate\\Support\\Facades\\Hash',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Auth\\PasswordUpdateRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/PasswordResetLinkController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Auth\\PasswordResetLinkController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'create',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Display the password reset link request view.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'password' => 'Illuminate\\Support\\Facades\\Password',
              'validationexception' => 'Illuminate\\Validation\\ValidationException',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response|Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'store',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Handle an incoming password reset link request.
     *
     * @throws \\Illuminate\\Validation\\ValidationException
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'password' => 'Illuminate\\Support\\Facades\\Password',
              'validationexception' => 'Illuminate\\Validation\\ValidationException',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/RegisteredUserController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Auth\\RegisteredUserController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'sessionDataMigration',
               'type' => 'App\\Services\\SessionDataMigrationService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'planLimitService',
               'type' => 'App\\Services\\PlanLimitService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'auditService',
               'type' => 'App\\Services\\AuditService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'create',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Display the registration view.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'registerrequest' => 'App\\Http\\Requests\\Auth\\RegisterRequest',
              'user' => 'App\\Models\\User',
              'auditservice' => 'App\\Services\\AuditService',
              'planlimitservice' => 'App\\Services\\PlanLimitService',
              'sessiondatamigrationservice' => 'App\\Services\\SessionDataMigrationService',
              'registered' => 'Illuminate\\Auth\\Events\\Registered',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'hash' => 'Illuminate\\Support\\Facades\\Hash',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response|Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'store',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Handle an incoming registration request.
     *
     * @throws \\Illuminate\\Validation\\ValidationException
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'registerrequest' => 'App\\Http\\Requests\\Auth\\RegisterRequest',
              'user' => 'App\\Models\\User',
              'auditservice' => 'App\\Services\\AuditService',
              'planlimitservice' => 'App\\Services\\PlanLimitService',
              'sessiondatamigrationservice' => 'App\\Services\\SessionDataMigrationService',
              'registered' => 'Illuminate\\Auth\\Events\\Registered',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'hash' => 'Illuminate\\Support\\Facades\\Hash',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Auth\\RegisterRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/SocialAuthController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Auth\\SocialAuthController',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Social Authentication Controller
 *
 * Handles OAuth authentication via providers like Google and GitHub.
 * Only active when FEATURE_SOCIAL_AUTH=true in environment.
 *
 * Routes are registered in routes/auth.php when feature is enabled.
 */',
         'namespace' => 'App\\Http\\Controllers\\Auth',
         'uses' => 
        array (
          'controller' => 'App\\Http\\Controllers\\Controller',
          'socialauthservice' => 'App\\Services\\SocialAuthService',
          'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
          'request' => 'Illuminate\\Http\\Request',
          'auth' => 'Illuminate\\Support\\Facades\\Auth',
          'db' => 'Illuminate\\Support\\Facades\\DB',
          'socialite' => 'Laravel\\Socialite\\Facades\\Socialite',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'providers',
          ),
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Supported OAuth providers.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'socialauthservice' => 'App\\Services\\SocialAuthService',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'socialite' => 'Laravel\\Socialite\\Facades\\Socialite',
            ),
             'constUses' => 
            array (
            ),
          )),
           'type' => 'array',
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'socialAuthService',
               'type' => 'App\\Services\\SocialAuthService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'redirect',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Redirect the user to the OAuth provider.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'socialauthservice' => 'App\\Services\\SocialAuthService',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'socialite' => 'Laravel\\Socialite\\Facades\\Socialite',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'provider',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'callback',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Handle the OAuth callback from the provider.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'socialauthservice' => 'App\\Services\\SocialAuthService',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'socialite' => 'Laravel\\Socialite\\Facades\\Socialite',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'provider',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'disconnect',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Disconnect a social account from the user\'s profile.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'socialauthservice' => 'App\\Services\\SocialAuthService',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'socialite' => 'Laravel\\Socialite\\Facades\\Socialite',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'provider',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/TwoFactorChallengeController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Auth\\TwoFactorChallengeController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'auditService',
               'type' => 'App\\Services\\AuditService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'create',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response|Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'store',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\TwoFactor\\TwoFactorChallengeRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Auth/VerifyEmailController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Auth\\VerifyEmailController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Mark the authenticated user\'s email address as verified.
     */',
             'namespace' => 'App\\Http\\Controllers\\Auth',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'verified' => 'Illuminate\\Auth\\Events\\Verified',
              'emailverificationrequest' => 'Illuminate\\Foundation\\Auth\\EmailVerificationRequest',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Foundation\\Auth\\EmailVerificationRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/BillingController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Billing\\BillingController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'billingService',
               'type' => 'App\\Services\\BillingService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'planLimitService',
               'type' => 'App\\Services\\PlanLimitService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'index',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/PricingController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Billing\\PricingController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'planLimitService',
               'type' => 'App\\Services\\PlanLimitService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/StripeWebhookController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Billing\\StripeWebhookController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Laravel\\Cashier\\Http\\Controllers\\WebhookController',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handleCustomerSubscriptionCreated',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'payload',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handleCustomerSubscriptionUpdated',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'payload',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handleCustomerSubscriptionDeleted',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'payload',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handleCustomerSubscriptionTrialWillEnd',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'payload',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handleInvoicePaymentSucceeded',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'payload',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handleInvoicePaymentFailed',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'payload',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handleInvoicePaymentActionRequired',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'payload',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handleCustomerUpdated',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'payload',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handleChargeRefunded',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'payload',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Billing/SubscriptionController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Billing\\SubscriptionController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'billingService',
               'type' => 'App\\Services\\BillingService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'auditService',
               'type' => 'App\\Services\\AuditService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'subscribe',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Billing\\SubscribeRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'cancel',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse|Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Billing\\CancelSubscriptionRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'resume',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse|Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'swap',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Billing\\SwapPlanRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'updateQuantity',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Billing\\UpdateQuantityRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'updatePaymentMethod',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\Billing\\UpdatePaymentMethodRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'portal',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ChartsController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\ChartsController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'index',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Controller.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Controller',
       'phpDoc' => NULL,
       'abstract' => true,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Foundation\\Auth\\Access\\AuthorizesRequests',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/DashboardController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\DashboardController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ExportController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\ExportController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'users',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\ExportRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/HealthCheckController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\HealthCheckController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'healthCheckService',
               'type' => 'App\\Services\\HealthCheckService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/OnboardingController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\OnboardingController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/ProfileController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\ProfileController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'auditService',
               'type' => 'App\\Services\\AuditService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'billingService',
               'type' => 'App\\Services\\BillingService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'edit',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Display the user\'s profile form.
     */',
             'namespace' => 'App\\Http\\Controllers',
             'uses' => 
            array (
              'deleteaccountrequest' => 'App\\Http\\Requests\\DeleteAccountRequest',
              'profileupdaterequest' => 'App\\Http\\Requests\\ProfileUpdateRequest',
              'cancelorphanedstripesubscription' => 'App\\Jobs\\CancelOrphanedStripeSubscription',
              'auditservice' => 'App\\Services\\AuditService',
              'billingservice' => 'App\\Services\\BillingService',
              'mustverifyemail' => 'Illuminate\\Contracts\\Auth\\MustVerifyEmail',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'redirect' => 'Illuminate\\Support\\Facades\\Redirect',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update the user\'s profile information.
     */',
             'namespace' => 'App\\Http\\Controllers',
             'uses' => 
            array (
              'deleteaccountrequest' => 'App\\Http\\Requests\\DeleteAccountRequest',
              'profileupdaterequest' => 'App\\Http\\Requests\\ProfileUpdateRequest',
              'cancelorphanedstripesubscription' => 'App\\Jobs\\CancelOrphanedStripeSubscription',
              'auditservice' => 'App\\Services\\AuditService',
              'billingservice' => 'App\\Services\\BillingService',
              'mustverifyemail' => 'Illuminate\\Contracts\\Auth\\MustVerifyEmail',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'redirect' => 'Illuminate\\Support\\Facades\\Redirect',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\ProfileUpdateRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'destroy',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Delete the user\'s account.
     */',
             'namespace' => 'App\\Http\\Controllers',
             'uses' => 
            array (
              'deleteaccountrequest' => 'App\\Http\\Requests\\DeleteAccountRequest',
              'profileupdaterequest' => 'App\\Http\\Requests\\ProfileUpdateRequest',
              'cancelorphanedstripesubscription' => 'App\\Jobs\\CancelOrphanedStripeSubscription',
              'auditservice' => 'App\\Services\\AuditService',
              'billingservice' => 'App\\Services\\BillingService',
              'mustverifyemail' => 'Illuminate\\Contracts\\Auth\\MustVerifyEmail',
              'redirectresponse' => 'Illuminate\\Http\\RedirectResponse',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'redirect' => 'Illuminate\\Support\\Facades\\Redirect',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\DeleteAccountRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/SeoController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\SeoController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'robots',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'sitemap',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/ApiTokenPageController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Settings\\ApiTokenPageController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/TwoFactorController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Settings\\TwoFactorController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'auditService',
               'type' => 'App\\Services\\AuditService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'index',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'enable',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'confirm',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\TwoFactor\\ConfirmTwoFactorRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'disable',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'App\\Http\\Requests\\TwoFactor\\DisableTwoFactorRequest',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'recoveryCodes',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'regenerateRecoveryCodes',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\RedirectResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Settings/WebhookPageController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Settings\\WebhookPageController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/Webhook/IncomingWebhookController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Webhook\\IncomingWebhookController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'incomingWebhookService',
               'type' => 'App\\Services\\IncomingWebhookService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'provider',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Controllers/WelcomeController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\WelcomeController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__invoke',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/EnsureIsAdmin.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Middleware\\EnsureIsAdmin',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'auditService',
               'type' => 'App\\Services\\AuditService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'next',
               'type' => 'Closure',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/EnsureOnboardingCompleted.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Middleware\\EnsureOnboardingCompleted',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'next',
               'type' => 'Closure',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/EnsureSubscribed.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Middleware\\EnsureSubscribed',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'billingService',
               'type' => 'App\\Services\\BillingService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Handle an incoming request.
     *
     * Usage:
     *   middleware(\'subscribed\')        — any active subscription
     *   middleware(\'subscribed:team\')   — team tier or above
     *   middleware(\'subscribed:enterprise\') — enterprise only
     */',
             'namespace' => 'App\\Http\\Middleware',
             'uses' => 
            array (
              'billingservice' => 'App\\Services\\BillingService',
              'closure' => 'Closure',
              'request' => 'Illuminate\\Http\\Request',
              'response' => 'Symfony\\Component\\HttpFoundation\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'next',
               'type' => 'Closure',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'minimumTier',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/HandleInertiaRequests.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Middleware\\HandleInertiaRequests',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Inertia\\Middleware',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'rootView',
          ),
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */',
             'namespace' => 'App\\Http\\Middleware',
             'uses' => 
            array (
              'billingservice' => 'App\\Services\\BillingService',
              'featureflagservice' => 'App\\Services\\FeatureFlagService',
              'request' => 'Illuminate\\Http\\Request',
              'middleware' => 'Inertia\\Middleware',
            ),
             'constUses' => 
            array (
            ),
          )),
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'billingService',
               'type' => 'App\\Services\\BillingService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'featureFlagService',
               'type' => 'App\\Services\\FeatureFlagService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'version',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Determine the current asset version.
     */',
             'namespace' => 'App\\Http\\Middleware',
             'uses' => 
            array (
              'billingservice' => 'App\\Services\\BillingService',
              'featureflagservice' => 'App\\Services\\FeatureFlagService',
              'request' => 'Illuminate\\Http\\Request',
              'middleware' => 'Inertia\\Middleware',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'share',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */',
             'namespace' => 'App\\Http\\Middleware',
             'uses' => 
            array (
              'billingservice' => 'App\\Services\\BillingService',
              'featureflagservice' => 'App\\Services\\FeatureFlagService',
              'request' => 'Illuminate\\Http\\Request',
              'middleware' => 'Inertia\\Middleware',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/RateLimitHeaders.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Middleware\\RateLimitHeaders',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'next',
               'type' => 'Closure',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/RequestIdMiddleware.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Middleware\\RequestIdMiddleware',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'next',
               'type' => 'Closure',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/SecurityHeaders.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Middleware\\SecurityHeaders',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'next',
               'type' => 'Closure',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Middleware/VerifyWebhookSignature.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Middleware\\VerifyWebhookSignature',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'next',
               'type' => 'Closure',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminAuditLogIndexRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Admin\\AdminAuditLogIndexRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminExportRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Admin\\AdminExportRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminFeatureFlagRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminFeatureFlagUserRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Admin\\AdminFeatureFlagUserRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminSubscriptionIndexRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Admin\\AdminSubscriptionIndexRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Admin/AdminUserIndexRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Admin\\AdminUserIndexRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Api/CreateTokenRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Api\\CreateTokenRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */',
             'namespace' => 'App\\Http\\Requests\\Api',
             'uses' => 
            array (
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'rule' => 'Illuminate\\Validation\\Rule',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'messages',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, string>
     */',
             'namespace' => 'App\\Http\\Requests\\Api',
             'uses' => 
            array (
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'rule' => 'Illuminate\\Validation\\Rule',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Api/UpdateSettingRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Api\\UpdateSettingRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */',
             'namespace' => 'App\\Http\\Requests\\Api',
             'uses' => 
            array (
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'rule' => 'Illuminate\\Validation\\Rule',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'messages',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, string>
     */',
             'namespace' => 'App\\Http\\Requests\\Api',
             'uses' => 
            array (
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'rule' => 'Illuminate\\Validation\\Rule',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Auth/LoginRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Auth\\LoginRequest',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Login Form Request
 *
 * Handles validation and authentication for login requests.
 * Includes rate limiting to prevent brute force attacks.
 */',
         'namespace' => 'App\\Http\\Requests\\Auth',
         'uses' => 
        array (
          'lockout' => 'Illuminate\\Auth\\Events\\Lockout',
          'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
          'auth' => 'Illuminate\\Support\\Facades\\Auth',
          'ratelimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
          'str' => 'Illuminate\\Support\\Str',
          'validationexception' => 'Illuminate\\Validation\\ValidationException',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Determine if the user is authorized to make this request.
     */',
             'namespace' => 'App\\Http\\Requests\\Auth',
             'uses' => 
            array (
              'lockout' => 'Illuminate\\Auth\\Events\\Lockout',
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'ratelimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
              'str' => 'Illuminate\\Support\\Str',
              'validationexception' => 'Illuminate\\Validation\\ValidationException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */',
             'namespace' => 'App\\Http\\Requests\\Auth',
             'uses' => 
            array (
              'lockout' => 'Illuminate\\Auth\\Events\\Lockout',
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'ratelimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
              'str' => 'Illuminate\\Support\\Str',
              'validationexception' => 'Illuminate\\Validation\\ValidationException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authenticate',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Attempt to authenticate the request\'s credentials.
     *
     * @throws \\Illuminate\\Validation\\ValidationException
     */',
             'namespace' => 'App\\Http\\Requests\\Auth',
             'uses' => 
            array (
              'lockout' => 'Illuminate\\Auth\\Events\\Lockout',
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'ratelimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
              'str' => 'Illuminate\\Support\\Str',
              'validationexception' => 'Illuminate\\Validation\\ValidationException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'ensureIsNotRateLimited',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Ensure the login request is not rate limited.
     *
     * @throws \\Illuminate\\Validation\\ValidationException
     */',
             'namespace' => 'App\\Http\\Requests\\Auth',
             'uses' => 
            array (
              'lockout' => 'Illuminate\\Auth\\Events\\Lockout',
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'ratelimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
              'str' => 'Illuminate\\Support\\Str',
              'validationexception' => 'Illuminate\\Validation\\ValidationException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'throttleKey',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the rate limiting throttle key for the request.
     */',
             'namespace' => 'App\\Http\\Requests\\Auth',
             'uses' => 
            array (
              'lockout' => 'Illuminate\\Auth\\Events\\Lockout',
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'ratelimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
              'str' => 'Illuminate\\Support\\Str',
              'validationexception' => 'Illuminate\\Validation\\ValidationException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Auth/PasswordUpdateRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Auth\\PasswordUpdateRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */',
             'namespace' => 'App\\Http\\Requests\\Auth',
             'uses' => 
            array (
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'password' => 'Illuminate\\Validation\\Rules\\Password',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Auth/RegisterRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Auth\\RegisterRequest',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Register Form Request
 *
 * Handles validation for user registration requests.
 */',
         'namespace' => 'App\\Http\\Requests\\Auth',
         'uses' => 
        array (
          'user' => 'App\\Models\\User',
          'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
          'rules' => 'Illuminate\\Validation\\Rules',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Determine if the user is authorized to make this request.
     */',
             'namespace' => 'App\\Http\\Requests\\Auth',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'rules' => 'Illuminate\\Validation\\Rules',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */',
             'namespace' => 'App\\Http\\Requests\\Auth',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'rules' => 'Illuminate\\Validation\\Rules',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'messages',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */',
             'namespace' => 'App\\Http\\Requests\\Auth',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'rules' => 'Illuminate\\Validation\\Rules',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/CancelSubscriptionRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Billing\\CancelSubscriptionRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */',
             'namespace' => 'App\\Http\\Requests\\Billing',
             'uses' => 
            array (
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/Concerns/HasPriceValidation.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedTraitNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Billing\\Concerns\\HasPriceValidation',
       'phpDoc' => NULL,
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/SubscribeRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Billing\\SubscribeRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'App\\Http\\Requests\\Billing\\Concerns\\HasPriceValidation',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */',
             'namespace' => 'App\\Http\\Requests\\Billing',
             'uses' => 
            array (
              'haspricevalidation' => 'App\\Http\\Requests\\Billing\\Concerns\\HasPriceValidation',
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'rule' => 'Illuminate\\Validation\\Rule',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/SwapPlanRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Billing\\SwapPlanRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'App\\Http\\Requests\\Billing\\Concerns\\HasPriceValidation',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */',
             'namespace' => 'App\\Http\\Requests\\Billing',
             'uses' => 
            array (
              'haspricevalidation' => 'App\\Http\\Requests\\Billing\\Concerns\\HasPriceValidation',
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'rule' => 'Illuminate\\Validation\\Rule',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/UpdatePaymentMethodRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Billing\\UpdatePaymentMethodRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */',
             'namespace' => 'App\\Http\\Requests\\Billing',
             'uses' => 
            array (
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Billing/UpdateQuantityRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Billing\\UpdateQuantityRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */',
             'namespace' => 'App\\Http\\Requests\\Billing',
             'uses' => 
            array (
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/DeleteAccountRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\DeleteAccountRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */',
             'namespace' => 'App\\Http\\Requests',
             'uses' => 
            array (
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/ExportRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\ExportRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, mixed>
     */',
             'namespace' => 'App\\Http\\Requests',
             'uses' => 
            array (
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/FileUploadRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\FileUploadRequest',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Base class for file upload form requests.
 *
 * Subclasses must implement authorize() and rules(), using fileRules() for
 * standard file validation. Example:
 *
 *   public function authorize(): bool { return true; }
 *   public function rules(): array { return $this->fileRules([\'jpeg\', \'png\'], 5120); }
 */',
         'namespace' => 'App\\Http\\Requests',
         'uses' => 
        array (
          'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => true,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'fileRules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Common file validation rules.
     *
     * @param  array<int, string>  $mimeTypes  Accepted MIME types
     * @param  int  $maxKilobytes  Maximum file size in KB
     * @return array<string, array<int, string>>
     */',
             'namespace' => 'App\\Http\\Requests',
             'uses' => 
            array (
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'mimeTypes',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'maxKilobytes',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/ProfileUpdateRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\ProfileUpdateRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */',
             'namespace' => 'App\\Http\\Requests',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
              'rule' => 'Illuminate\\Validation\\Rule',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/TwoFactor/ConfirmTwoFactorRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\TwoFactor\\ConfirmTwoFactorRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, mixed>
     */',
             'namespace' => 'App\\Http\\Requests\\TwoFactor',
             'uses' => 
            array (
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/TwoFactor/DisableTwoFactorRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\TwoFactor\\DisableTwoFactorRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, mixed>
     */',
             'namespace' => 'App\\Http\\Requests\\TwoFactor',
             'uses' => 
            array (
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/TwoFactor/TwoFactorChallengeRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\TwoFactor\\TwoFactorChallengeRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, mixed>
     */',
             'namespace' => 'App\\Http\\Requests\\TwoFactor',
             'uses' => 
            array (
              'formrequest' => 'Illuminate\\Foundation\\Http\\FormRequest',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'withValidator',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'validator',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Webhook/CreateWebhookEndpointRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Webhook\\CreateWebhookEndpointRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Http/Requests/Webhook/UpdateWebhookEndpointRequest.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Requests\\Webhook\\UpdateWebhookEndpointRequest',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Http\\FormRequest',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'authorize',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rules',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/CancelOrphanedStripeSubscription.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Jobs\\CancelOrphanedStripeSubscription',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
        0 => 'Illuminate\\Contracts\\Queue\\ShouldQueue',
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Foundation\\Queue\\Queueable',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'tries',
          ),
           'phpDoc' => NULL,
           'type' => 'int',
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'backoff',
          ),
           'phpDoc' => NULL,
           'type' => 'int',
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'stripeCustomerId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userId',
               'type' => '?int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'failed',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'e',
               'type' => 'Throwable',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/DispatchWebhookJob.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Jobs\\DispatchWebhookJob',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
        0 => 'Illuminate\\Contracts\\Queue\\ShouldQueue',
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Foundation\\Queue\\Queueable',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'timeout',
          ),
           'phpDoc' => NULL,
           'type' => 'int',
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'tries',
          ),
           'phpDoc' => NULL,
           'type' => 'int',
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'backoff',
          ),
           'phpDoc' => NULL,
           'type' => 'array',
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'deliveryId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'webhookService',
               'type' => 'App\\Services\\WebhookService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Jobs/PersistAuditLog.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Jobs\\PersistAuditLog',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
        0 => 'Illuminate\\Contracts\\Queue\\ShouldQueue',
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Foundation\\Queue\\Queueable',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'tries',
          ),
           'phpDoc' => NULL,
           'type' => 'int',
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'event',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userId',
               'type' => '?int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'ip',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userAgent',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'metadata',
               'type' => '?array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Listeners/SendEmailVerificationNotification.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Listeners\\SendEmailVerificationNotification',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
        0 => 'Illuminate\\Contracts\\Queue\\ShouldQueue',
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'event',
               'type' => 'Illuminate\\Auth\\Events\\Registered',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/AuditLog.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\AuditLog',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'UPDATED_AT',
               'value' => 'null',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'user',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'scopeByUser',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Builder',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => 'Illuminate\\Database\\Eloquent\\Builder',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'scopeByEvent',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Builder',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => 'Illuminate\\Database\\Eloquent\\Builder',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'event',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'scopeRecent',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Builder',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => 'Illuminate\\Database\\Eloquent\\Builder',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'days',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/FeatureFlagOverride.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\FeatureFlagOverride',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'user',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the user this override applies to (null for global overrides).
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'changedByUser',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the admin user who made this change.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'scopeGlobal',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Scope to global overrides (user_id = null).
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'scopeForUser',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Scope to user-specific overrides.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'scopeForFlag',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Scope to a specific flag.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'flag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/IncomingWebhook.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\IncomingWebhook',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/SocialAccount.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\SocialAccount',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Social Account Model
 *
 * Stores OAuth provider credentials for social login.
 * Only used when FEATURE_SOCIAL_AUTH=true
 */',
         'namespace' => 'App\\Models',
         'uses' => 
        array (
          'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
          'model' => 'Illuminate\\Database\\Eloquent\\Model',
          'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * The attributes that are mass assignable.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'hidden',
          ),
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * The attributes that should be hidden for serialization.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the attributes that should be cast.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'user',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the user that owns the social account.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isTokenExpired',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if the token is expired.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/User.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\User',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * User Model
 *
 * Implements MustVerifyEmail conditionally based on feature flag.
 * To disable email verification, remove "implements MustVerifyEmail"
 * or check config(\'features.email_verification.enabled\').
 */',
         'namespace' => 'App\\Models',
         'uses' => 
        array (
          'mustverifyemail' => 'Illuminate\\Contracts\\Auth\\MustVerifyEmail',
          'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
          'softdeletes' => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
          'authenticatable' => 'Illuminate\\Foundation\\Auth\\User',
          'notifiable' => 'Illuminate\\Notifications\\Notifiable',
          'twofactorauthenticatable' => 'Laragear\\TwoFactor\\Contracts\\TwoFactorAuthenticatable',
          'twofactorauthentication' => 'Laragear\\TwoFactor\\TwoFactorAuthentication',
          'billable' => 'Laravel\\Cashier\\Billable',
          'hasapitokens' => 'Laravel\\Sanctum\\HasApiTokens',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Auth\\User',
       'implements' => 
      array (
        0 => 'Illuminate\\Contracts\\Auth\\MustVerifyEmail',
        1 => 'Laragear\\TwoFactor\\Contracts\\TwoFactorAuthenticatable',
      ),
       'usedTraits' => 
      array (
        0 => 'Laravel\\Cashier\\Billable',
        1 => 'Laravel\\Sanctum\\HasApiTokens',
        2 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        3 => 'Illuminate\\Notifications\\Notifiable',
        4 => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
        5 => 'Laragear\\TwoFactor\\TwoFactorAuthentication',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'mustverifyemail' => 'Illuminate\\Contracts\\Auth\\MustVerifyEmail',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'softdeletes' => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
              'authenticatable' => 'Illuminate\\Foundation\\Auth\\User',
              'notifiable' => 'Illuminate\\Notifications\\Notifiable',
              'twofactorauthenticatable' => 'Laragear\\TwoFactor\\Contracts\\TwoFactorAuthenticatable',
              'twofactorauthentication' => 'Laragear\\TwoFactor\\TwoFactorAuthentication',
              'billable' => 'Laravel\\Cashier\\Billable',
              'hasapitokens' => 'Laravel\\Sanctum\\HasApiTokens',
            ),
             'constUses' => 
            array (
            ),
          )),
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'hidden',
          ),
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'mustverifyemail' => 'Illuminate\\Contracts\\Auth\\MustVerifyEmail',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'softdeletes' => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
              'authenticatable' => 'Illuminate\\Foundation\\Auth\\User',
              'notifiable' => 'Illuminate\\Notifications\\Notifiable',
              'twofactorauthenticatable' => 'Laragear\\TwoFactor\\Contracts\\TwoFactorAuthenticatable',
              'twofactorauthentication' => 'Laragear\\TwoFactor\\TwoFactorAuthentication',
              'billable' => 'Laravel\\Cashier\\Billable',
              'hasapitokens' => 'Laravel\\Sanctum\\HasApiTokens',
            ),
             'constUses' => 
            array (
            ),
          )),
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'mustverifyemail' => 'Illuminate\\Contracts\\Auth\\MustVerifyEmail',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'softdeletes' => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
              'authenticatable' => 'Illuminate\\Foundation\\Auth\\User',
              'notifiable' => 'Illuminate\\Notifications\\Notifiable',
              'twofactorauthenticatable' => 'Laragear\\TwoFactor\\Contracts\\TwoFactorAuthenticatable',
              'twofactorauthentication' => 'Laragear\\TwoFactor\\TwoFactorAuthentication',
              'billable' => 'Laravel\\Cashier\\Billable',
              'hasapitokens' => 'Laravel\\Sanctum\\HasApiTokens',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isAdmin',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'hasPassword',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if user has a password set.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'mustverifyemail' => 'Illuminate\\Contracts\\Auth\\MustVerifyEmail',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'softdeletes' => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
              'authenticatable' => 'Illuminate\\Foundation\\Auth\\User',
              'notifiable' => 'Illuminate\\Notifications\\Notifiable',
              'twofactorauthenticatable' => 'Laragear\\TwoFactor\\Contracts\\TwoFactorAuthenticatable',
              'twofactorauthentication' => 'Laragear\\TwoFactor\\TwoFactorAuthentication',
              'billable' => 'Laravel\\Cashier\\Billable',
              'hasapitokens' => 'Laravel\\Sanctum\\HasApiTokens',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'updateLastLogin',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update last login timestamp.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'mustverifyemail' => 'Illuminate\\Contracts\\Auth\\MustVerifyEmail',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'softdeletes' => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
              'authenticatable' => 'Illuminate\\Foundation\\Auth\\User',
              'notifiable' => 'Illuminate\\Notifications\\Notifiable',
              'twofactorauthenticatable' => 'Laragear\\TwoFactor\\Contracts\\TwoFactorAuthenticatable',
              'twofactorauthentication' => 'Laragear\\TwoFactor\\TwoFactorAuthentication',
              'billable' => 'Laravel\\Cashier\\Billable',
              'hasapitokens' => 'Laravel\\Sanctum\\HasApiTokens',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'socialAccounts',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the social accounts for the user.
     * (Only used when social_auth feature is enabled)
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'mustverifyemail' => 'Illuminate\\Contracts\\Auth\\MustVerifyEmail',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'softdeletes' => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
              'authenticatable' => 'Illuminate\\Foundation\\Auth\\User',
              'notifiable' => 'Illuminate\\Notifications\\Notifiable',
              'twofactorauthenticatable' => 'Laragear\\TwoFactor\\Contracts\\TwoFactorAuthenticatable',
              'twofactorauthentication' => 'Laragear\\TwoFactor\\TwoFactorAuthentication',
              'billable' => 'Laravel\\Cashier\\Billable',
              'hasapitokens' => 'Laravel\\Sanctum\\HasApiTokens',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'webhookEndpoints',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the webhook endpoints for the user.
     * (Only used when webhooks feature is enabled)
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'mustverifyemail' => 'Illuminate\\Contracts\\Auth\\MustVerifyEmail',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'softdeletes' => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
              'authenticatable' => 'Illuminate\\Foundation\\Auth\\User',
              'notifiable' => 'Illuminate\\Notifications\\Notifiable',
              'twofactorauthenticatable' => 'Laragear\\TwoFactor\\Contracts\\TwoFactorAuthenticatable',
              'twofactorauthentication' => 'Laragear\\TwoFactor\\TwoFactorAuthentication',
              'billable' => 'Laravel\\Cashier\\Billable',
              'hasapitokens' => 'Laravel\\Sanctum\\HasApiTokens',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settings',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the settings for the user.
     * (Only used when user_settings feature is enabled)
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'mustverifyemail' => 'Illuminate\\Contracts\\Auth\\MustVerifyEmail',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'softdeletes' => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
              'authenticatable' => 'Illuminate\\Foundation\\Auth\\User',
              'notifiable' => 'Illuminate\\Notifications\\Notifiable',
              'twofactorauthenticatable' => 'Laragear\\TwoFactor\\Contracts\\TwoFactorAuthenticatable',
              'twofactorauthentication' => 'Laragear\\TwoFactor\\TwoFactorAuthentication',
              'billable' => 'Laravel\\Cashier\\Billable',
              'hasapitokens' => 'Laravel\\Sanctum\\HasApiTokens',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getSetting',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get a setting value for this user.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'mustverifyemail' => 'Illuminate\\Contracts\\Auth\\MustVerifyEmail',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'softdeletes' => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
              'authenticatable' => 'Illuminate\\Foundation\\Auth\\User',
              'notifiable' => 'Illuminate\\Notifications\\Notifiable',
              'twofactorauthenticatable' => 'Laragear\\TwoFactor\\Contracts\\TwoFactorAuthenticatable',
              'twofactorauthentication' => 'Laragear\\TwoFactor\\TwoFactorAuthentication',
              'billable' => 'Laravel\\Cashier\\Billable',
              'hasapitokens' => 'Laravel\\Sanctum\\HasApiTokens',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'mixed',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'default',
               'type' => '?mixed',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'setSetting',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Set a setting value for this user.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'mustverifyemail' => 'Illuminate\\Contracts\\Auth\\MustVerifyEmail',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'softdeletes' => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
              'authenticatable' => 'Illuminate\\Foundation\\Auth\\User',
              'notifiable' => 'Illuminate\\Notifications\\Notifiable',
              'twofactorauthenticatable' => 'Laragear\\TwoFactor\\Contracts\\TwoFactorAuthenticatable',
              'twofactorauthentication' => 'Laragear\\TwoFactor\\TwoFactorAuthentication',
              'billable' => 'Laravel\\Cashier\\Billable',
              'hasapitokens' => 'Laravel\\Sanctum\\HasApiTokens',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'mixed',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'value',
               'type' => 'mixed',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/UserSetting.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\UserSetting',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * User Setting Model
 *
 * Key-value store for user preferences.
 * Only used when FEATURE_USER_SETTINGS=true
 *
 * Common keys:
 * - \'theme\': \'light\' | \'dark\' | \'system\'
 * - \'timezone\': IANA timezone string
 * - \'notifications_email\': boolean
 */',
         'namespace' => 'App\\Models',
         'uses' => 
        array (
          'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
          'model' => 'Illuminate\\Database\\Eloquent\\Model',
          'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
          'cache' => 'Illuminate\\Support\\Facades\\Cache',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * The attributes that are mass assignable.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'user',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the user that owns the setting.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getValue',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get a setting value for a user.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'mixed',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'default',
               'type' => '?mixed',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'setValue',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Set a setting value for a user.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'static',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'value',
               'type' => 'mixed',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'deleteSetting',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Delete a setting for a user.
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/WebhookDelivery.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\WebhookDelivery',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'endpoint',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Models/WebhookEndpoint.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\WebhookEndpoint',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\SoftDeletes',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'user',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'deliveries',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Notifications/IncompletePaymentReminder.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Notifications\\IncompletePaymentReminder',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Notifications\\Notification',
       'implements' => 
      array (
        0 => 'Illuminate\\Contracts\\Queue\\ShouldQueue',
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Bus\\Queueable',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'confirmUrl',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'hoursRemaining',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'via',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<int, string>
     */',
             'namespace' => 'App\\Notifications',
             'uses' => 
            array (
              'queueable' => 'Illuminate\\Bus\\Queueable',
              'shouldqueue' => 'Illuminate\\Contracts\\Queue\\ShouldQueue',
              'mailmessage' => 'Illuminate\\Notifications\\Messages\\MailMessage',
              'notification' => 'Illuminate\\Notifications\\Notification',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'notifiable',
               'type' => 'object',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'toMail',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Notifications\\Messages\\MailMessage',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'notifiable',
               'type' => 'object',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'toArray',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, mixed>
     */',
             'namespace' => 'App\\Notifications',
             'uses' => 
            array (
              'queueable' => 'Illuminate\\Bus\\Queueable',
              'shouldqueue' => 'Illuminate\\Contracts\\Queue\\ShouldQueue',
              'mailmessage' => 'Illuminate\\Notifications\\Messages\\MailMessage',
              'notification' => 'Illuminate\\Notifications\\Notification',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'notifiable',
               'type' => 'object',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Notifications/PaymentFailedNotification.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Notifications\\PaymentFailedNotification',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Notifications\\Notification',
       'implements' => 
      array (
        0 => 'Illuminate\\Contracts\\Queue\\ShouldQueue',
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Bus\\Queueable',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'invoiceId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'subscriptionId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'via',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<int, string>
     */',
             'namespace' => 'App\\Notifications',
             'uses' => 
            array (
              'queueable' => 'Illuminate\\Bus\\Queueable',
              'shouldqueue' => 'Illuminate\\Contracts\\Queue\\ShouldQueue',
              'mailmessage' => 'Illuminate\\Notifications\\Messages\\MailMessage',
              'notification' => 'Illuminate\\Notifications\\Notification',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'notifiable',
               'type' => 'object',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'toMail',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Notifications\\Messages\\MailMessage',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'notifiable',
               'type' => 'object',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'toArray',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, mixed>
     */',
             'namespace' => 'App\\Notifications',
             'uses' => 
            array (
              'queueable' => 'Illuminate\\Bus\\Queueable',
              'shouldqueue' => 'Illuminate\\Contracts\\Queue\\ShouldQueue',
              'mailmessage' => 'Illuminate\\Notifications\\Messages\\MailMessage',
              'notification' => 'Illuminate\\Notifications\\Notification',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'notifiable',
               'type' => 'object',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Notifications/RefundProcessedNotification.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Notifications\\RefundProcessedNotification',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Notifications\\Notification',
       'implements' => 
      array (
        0 => 'Illuminate\\Contracts\\Queue\\ShouldQueue',
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Bus\\Queueable',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'chargeId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'amountRefunded',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'currency',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'reason',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'via',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<int, string>
     */',
             'namespace' => 'App\\Notifications',
             'uses' => 
            array (
              'queueable' => 'Illuminate\\Bus\\Queueable',
              'shouldqueue' => 'Illuminate\\Contracts\\Queue\\ShouldQueue',
              'mailmessage' => 'Illuminate\\Notifications\\Messages\\MailMessage',
              'notification' => 'Illuminate\\Notifications\\Notification',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'notifiable',
               'type' => 'object',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'toMail',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Notifications\\Messages\\MailMessage',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'notifiable',
               'type' => 'object',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'toArray',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, mixed>
     */',
             'namespace' => 'App\\Notifications',
             'uses' => 
            array (
              'queueable' => 'Illuminate\\Bus\\Queueable',
              'shouldqueue' => 'Illuminate\\Contracts\\Queue\\ShouldQueue',
              'mailmessage' => 'Illuminate\\Notifications\\Messages\\MailMessage',
              'notification' => 'Illuminate\\Notifications\\Notification',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'notifiable',
               'type' => 'object',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Policies/UserPolicy.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Policies\\UserPolicy',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Determine if the user can update their profile.
     */',
             'namespace' => 'App\\Policies',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'model',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'delete',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Determine if the user can delete their account.
     */',
             'namespace' => 'App\\Policies',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'model',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Providers/AppServiceProvider.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Providers\\AppServiceProvider',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Support\\ServiceProvider',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Register any application services.
     */',
             'namespace' => 'App\\Providers',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
              'userpolicy' => 'App\\Policies\\UserPolicy',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'gate' => 'Illuminate\\Support\\Facades\\Gate',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'url' => 'Illuminate\\Support\\Facades\\URL',
              'serviceprovider' => 'Illuminate\\Support\\ServiceProvider',
              'cashier' => 'Laravel\\Cashier\\Cashier',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'boot',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Bootstrap any application services.
     */',
             'namespace' => 'App\\Providers',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
              'userpolicy' => 'App\\Policies\\UserPolicy',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'gate' => 'Illuminate\\Support\\Facades\\Gate',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'url' => 'Illuminate\\Support\\Facades\\URL',
              'serviceprovider' => 'Illuminate\\Support\\ServiceProvider',
              'cashier' => 'Laravel\\Cashier\\Cashier',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Providers/EventServiceProvider.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Providers\\EventServiceProvider',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'listen',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'configureEmailVerification',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Override to prevent the framework from also registering its
     * synchronous SendEmailVerificationNotification listener.
     */',
             'namespace' => 'App\\Providers',
             'uses' => 
            array (
              'sendemailverificationnotification' => 'App\\Listeners\\SendEmailVerificationNotification',
              'registered' => 'Illuminate\\Auth\\Events\\Registered',
              'serviceprovider' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AdminBillingStatsService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\AdminBillingStatsService',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'billingService',
               'type' => 'App\\Services\\BillingService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getDashboardStats',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array{active_subscriptions: int, trialing: int, past_due: int, canceled: int, total_ever: int, mrr: float, churn_rate: float, trial_conversion_rate: float}
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'queryhelper' => 'App\\Helpers\\QueryHelper',
              'lengthawarepaginator' => 'Illuminate\\Pagination\\LengthAwarePaginator',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getTierDistribution',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/** @return array<int, array{tier: string, count: int}> */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'queryhelper' => 'App\\Helpers\\QueryHelper',
              'lengthawarepaginator' => 'Illuminate\\Pagination\\LengthAwarePaginator',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getStatusBreakdown',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/** @return array<int, array{status: string, count: int}> */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'queryhelper' => 'App\\Helpers\\QueryHelper',
              'lengthawarepaginator' => 'Illuminate\\Pagination\\LengthAwarePaginator',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getGrowthChart',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/** @return array<int, array{date: string, count: int}> */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'queryhelper' => 'App\\Helpers\\QueryHelper',
              'lengthawarepaginator' => 'Illuminate\\Pagination\\LengthAwarePaginator',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getTrialStats',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/** @return array{active_trials: int, expiring_soon: int} */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'queryhelper' => 'App\\Helpers\\QueryHelper',
              'lengthawarepaginator' => 'Illuminate\\Pagination\\LengthAwarePaginator',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getFilteredSubscriptions',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @param  array{search?: string, status?: string, tier?: string, sort?: string, dir?: string}  $validated
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'queryhelper' => 'App\\Helpers\\QueryHelper',
              'lengthawarepaginator' => 'Illuminate\\Pagination\\LengthAwarePaginator',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Pagination\\LengthAwarePaginator',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'validated',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/AuditService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\AuditService',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'logLogin',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => '?App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'logLogout',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => '?App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'logRegistration',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'log',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log a generic audit event. Metadata values should be scalar types
     * (strings, numbers, booleans) — never pass raw user input without sanitization.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'persistauditlog' => 'App\\Jobs\\PersistAuditLog',
              'user' => 'App\\Models\\User',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'event',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'context',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/BillingService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\BillingService',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'resolveUserTier',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Resolve which plan tier a user belongs to based on their active subscription\'s Stripe price.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'concurrentoperationexception' => 'App\\Exceptions\\ConcurrentOperationException',
              'user' => 'App\\Models\\User',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'subscription' => 'Laravel\\Cashier\\Subscription',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'createSubscription',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create a new subscription for the user.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'concurrentoperationexception' => 'App\\Exceptions\\ConcurrentOperationException',
              'user' => 'App\\Models\\User',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'subscription' => 'Laravel\\Cashier\\Subscription',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Laravel\\Cashier\\Subscription',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'priceId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'paymentMethod',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'coupon',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'quantity',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'cancelSubscription',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Cancel the user\'s subscription.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'concurrentoperationexception' => 'App\\Exceptions\\ConcurrentOperationException',
              'user' => 'App\\Models\\User',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'subscription' => 'Laravel\\Cashier\\Subscription',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Laravel\\Cashier\\Subscription',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'immediately',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'resumeSubscription',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Resume a canceled subscription during grace period.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'concurrentoperationexception' => 'App\\Exceptions\\ConcurrentOperationException',
              'user' => 'App\\Models\\User',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'subscription' => 'Laravel\\Cashier\\Subscription',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Laravel\\Cashier\\Subscription',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'swapPlan',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Swap the subscription to a new plan/price.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'concurrentoperationexception' => 'App\\Exceptions\\ConcurrentOperationException',
              'user' => 'App\\Models\\User',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'subscription' => 'Laravel\\Cashier\\Subscription',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Laravel\\Cashier\\Subscription',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'newPriceId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'updateQuantity',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update the subscription quantity (seat count for team/enterprise plans).
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'concurrentoperationexception' => 'App\\Exceptions\\ConcurrentOperationException',
              'user' => 'App\\Models\\User',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'subscription' => 'Laravel\\Cashier\\Subscription',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Laravel\\Cashier\\Subscription',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'quantity',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'updatePaymentMethod',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update the user\'s default payment method.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'concurrentoperationexception' => 'App\\Exceptions\\ConcurrentOperationException',
              'user' => 'App\\Models\\User',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'subscription' => 'Laravel\\Cashier\\Subscription',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'paymentMethodId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getBillingPortalUrl',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the Stripe billing portal URL.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'concurrentoperationexception' => 'App\\Exceptions\\ConcurrentOperationException',
              'user' => 'App\\Models\\User',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'subscription' => 'Laravel\\Cashier\\Subscription',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'returnUrl',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getSubscriptionStatus',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get comprehensive subscription status for the user.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'concurrentoperationexception' => 'App\\Exceptions\\ConcurrentOperationException',
              'user' => 'App\\Models\\User',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'subscription' => 'Laravel\\Cashier\\Subscription',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'validateSeatCount',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Validate the seat count for a given tier.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'concurrentoperationexception' => 'App\\Exceptions\\ConcurrentOperationException',
              'user' => 'App\\Models\\User',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'subscription' => 'Laravel\\Cashier\\Subscription',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'tier',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'quantity',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'resolveTierFromPrice',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Resolve the tier for a given Stripe price ID.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'concurrentoperationexception' => 'App\\Exceptions\\ConcurrentOperationException',
              'user' => 'App\\Models\\User',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'subscription' => 'Laravel\\Cashier\\Subscription',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'priceId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/FeatureFlagService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\FeatureFlagService',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'resolve',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Resolve a single feature flag for a given user.
     *
     * Resolution order:
     * 1. User-specific override (if user provided and override exists)
     * 2. Global override (if exists)
     * 3. Config default
     *
     * Special case: Route-dependent flags with env=false return false
     * regardless of DB overrides (routes aren\'t registered).
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'featureflagoverride' => 'App\\Models\\FeatureFlagOverride',
              'user' => 'App\\Models\\User',
              'queryexception' => 'Illuminate\\Database\\QueryException',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'flag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => '?App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'resolveAll',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Resolve all defined feature flags for a given user.
     *
     * @return array<string, bool>
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'featureflagoverride' => 'App\\Models\\FeatureFlagOverride',
              'user' => 'App\\Models\\User',
              'queryexception' => 'Illuminate\\Database\\QueryException',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => '?App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getDefinedFlags',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get all defined feature flags from config.
     * Only returns entries that have an \'enabled\' key (excludes nested config like pagination).
     *
     * @return array<string, bool>
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'featureflagoverride' => 'App\\Models\\FeatureFlagOverride',
              'user' => 'App\\Models\\User',
              'queryexception' => 'Illuminate\\Database\\QueryException',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getAdminSummary',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get admin summary data for all flags.
     *
     * @return array<int, array{
     *     flag: string,
     *     env_default: bool,
     *     global_override: bool|null,
     *     effective: bool,
     *     user_override_count: int,
     *     is_protected: bool,
     *     is_route_dependent: bool,
     *     reason: string|null,
     *     updated_at: string|null
     * }>
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'featureflagoverride' => 'App\\Models\\FeatureFlagOverride',
              'user' => 'App\\Models\\User',
              'queryexception' => 'Illuminate\\Database\\QueryException',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getTargetedUsers',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get users with overrides for a specific flag.
     * Excludes soft-deleted users.
     *
     * @return array<int, array{user_id: int, name: string, email: string, enabled: bool}>
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'featureflagoverride' => 'App\\Models\\FeatureFlagOverride',
              'user' => 'App\\Models\\User',
              'queryexception' => 'Illuminate\\Database\\QueryException',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'flag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'setGlobalOverride',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Set or update a global override.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'featureflagoverride' => 'App\\Models\\FeatureFlagOverride',
              'user' => 'App\\Models\\User',
              'queryexception' => 'Illuminate\\Database\\QueryException',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'flag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'enabled',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'reason',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'adminUser',
               'type' => '?App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'removeGlobalOverride',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Remove a global override (reverts to config default).
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'featureflagoverride' => 'App\\Models\\FeatureFlagOverride',
              'user' => 'App\\Models\\User',
              'queryexception' => 'Illuminate\\Database\\QueryException',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'flag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'setUserOverride',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Set or update a user-specific override.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'featureflagoverride' => 'App\\Models\\FeatureFlagOverride',
              'user' => 'App\\Models\\User',
              'queryexception' => 'Illuminate\\Database\\QueryException',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'flag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'enabled',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'reason',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'adminUser',
               'type' => '?App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'removeUserOverride',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Remove a user-specific override.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'featureflagoverride' => 'App\\Models\\FeatureFlagOverride',
              'user' => 'App\\Models\\User',
              'queryexception' => 'Illuminate\\Database\\QueryException',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'flag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'removeAllUserOverrides',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Remove all user overrides for a flag.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'featureflagoverride' => 'App\\Models\\FeatureFlagOverride',
              'user' => 'App\\Models\\User',
              'queryexception' => 'Illuminate\\Database\\QueryException',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'flag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'searchUsers',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Search users by name or email for targeting.
     *
     * @return array<int, array{id: int, name: string, email: string}>
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'admincachekey' => 'App\\Enums\\AdminCacheKey',
              'featureflagoverride' => 'App\\Models\\FeatureFlagOverride',
              'user' => 'App\\Models\\User',
              'queryexception' => 'Illuminate\\Database\\QueryException',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'invalidargumentexception' => 'InvalidArgumentException',
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'limit',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/HealthCheckService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\HealthCheckService',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'customChecks',
          ),
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/** @var array<string, callable> */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'queue' => 'Illuminate\\Support\\Facades\\Queue',
            ),
             'constUses' => 
            array (
            ),
          )),
           'type' => 'array',
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'registerCheck',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Register a custom health check. Downstream projects call this to add
     * their own checks (Redis, external APIs, etc.) without modifying the service.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'queue' => 'Illuminate\\Support\\Facades\\Queue',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'check',
               'type' => 'callable',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'runAllChecks',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Run all health checks and return aggregated results.
     * Results are cached for 5 seconds to prevent database strain from rapid refreshes.
     *
     * @return array{status: string, checks: array, timestamp: string}
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'db' => 'Illuminate\\Support\\Facades\\DB',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'queue' => 'Illuminate\\Support\\Facades\\Queue',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'checkDatabase',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'checkCache',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'checkQueue',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'checkDisk',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'timedCheck',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'check',
               'type' => 'callable',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/IncomingWebhookService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\IncomingWebhookService',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'process',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Process an incoming webhook, storing it for idempotency.
     *
     * @return IncomingWebhook|null Returns null if already processed (idempotent)
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'incomingwebhook' => 'App\\Models\\IncomingWebhook',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?App\\Models\\IncomingWebhook',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'provider',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'externalId',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'eventType',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'payload',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isProcessed',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if a webhook has already been processed (idempotency check).
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'incomingwebhook' => 'App\\Models\\IncomingWebhook',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'provider',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'externalId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/PlanLimitService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\PlanLimitService',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'billingService',
               'type' => 'App\\Services\\BillingService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isTrialEnabled',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if trials are enabled in configuration.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
              'carbon' => 'Carbon\\Carbon',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'startTrial',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Start a trial period for a user.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
              'carbon' => 'Carbon\\Carbon',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isOnTrial',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if user is currently in trial period.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
              'carbon' => 'Carbon\\Carbon',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'trialDaysRemaining',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the number of days remaining in trial.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
              'carbon' => 'Carbon\\Carbon',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getUserPlan',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the user\'s current plan tier (cached).
     *
     * Resolves tier from: trial > active subscription (with grace period) > free.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
              'carbon' => 'Carbon\\Carbon',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'invalidateUserPlanCache',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Invalidate the cached plan tier for a user.
     * Call after subscription state changes (webhooks, cancel, resume, etc.).
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
              'carbon' => 'Carbon\\Carbon',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getLimit',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get a specific limit for the user based on their plan.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
              'carbon' => 'Carbon\\Carbon',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?int',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'limitKey',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'canPerform',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if user can perform an action based on limits.
     *
     * @param  int  $currentCount  Current usage count
     * @return bool True if under limit, false if at/over limit
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
              'carbon' => 'Carbon\\Carbon',
              'cache' => 'Illuminate\\Support\\Facades\\Cache',
              'log' => 'Illuminate\\Support\\Facades\\Log',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'limitKey',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'currentCount',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SessionDataMigrationService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\SessionDataMigrationService',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Session Data Migration Service
 *
 * Placeholder service for migrating anonymous session data to a user account.
 *
 * Use this pattern when your app allows anonymous users to perform actions
 * (like creating a cart, running a scan, building a configuration) that
 * should be preserved when they register or log in.
 *
 * Example implementations:
 * - E-commerce: Migrate guest cart items to user\'s cart
 * - SaaS: Migrate anonymous scan results to user\'s project
 * - Wizard: Migrate form progress to user\'s draft
 */',
         'namespace' => 'App\\Services',
         'uses' => 
        array (
          'user' => 'App\\Models\\User',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'sessionKey',
          ),
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Session key for storing anonymous user data.
     * Override this in your implementation.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
            ),
             'constUses' => 
            array (
            ),
          )),
           'type' => 'string',
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'hasSessionData',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if there is session data to migrate.
     *
     * Override this method to check your specific session keys.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getSessionDataSummary',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get a summary of the session data for display during registration.
     *
     * This is shown to users so they know their data will be preserved.
     *
     * @return array{items_count: int, description: string}|null
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'migrateSessionData',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Migrate session data to the user\'s account.
     *
     * Called after successful registration or login.
     *
     * @return array{migrated: bool, items_count: int, project_items: int}
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'clearSessionData',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Clear session data without migrating.
     *
     * Use when user explicitly chooses not to migrate,
     * or when session data is no longer valid.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'user' => 'App\\Models\\User',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/SocialAuthService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\SocialAuthService',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Social Authentication Service
 *
 * Handles the logic for OAuth user management:
 * - Finding existing users by social account or email
 * - Creating new users from OAuth data
 * - Linking/updating social account credentials
 *
 * Only used when FEATURE_SOCIAL_AUTH=true
 */',
         'namespace' => 'App\\Services',
         'uses' => 
        array (
          'socialaccount' => 'App\\Models\\SocialAccount',
          'user' => 'App\\Models\\User',
          'socialuser' => 'Laravel\\Socialite\\Contracts\\User',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'findOrCreateUser',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Find an existing user or create a new one from OAuth data.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'socialaccount' => 'App\\Models\\SocialAccount',
              'user' => 'App\\Models\\User',
              'socialuser' => 'Laravel\\Socialite\\Contracts\\User',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\User',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'socialUser',
               'type' => 'Laravel\\Socialite\\Contracts\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'provider',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'linkSocialAccount',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Link or update a social account for a user.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'socialaccount' => 'App\\Models\\SocialAccount',
              'user' => 'App\\Models\\User',
              'socialuser' => 'Laravel\\Socialite\\Contracts\\User',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\SocialAccount',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'socialUser',
               'type' => 'Laravel\\Socialite\\Contracts\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'provider',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Services/WebhookService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\WebhookService',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'dispatch',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Dispatch a webhook event to all matching active endpoints for a user.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'dispatchwebhookjob' => 'App\\Jobs\\DispatchWebhookJob',
              'webhookdelivery' => 'App\\Models\\WebhookDelivery',
              'webhookendpoint' => 'App\\Models\\WebhookEndpoint',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'eventType',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'payload',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'dispatchToEndpoint',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Dispatch a webhook event directly to a specific endpoint, bypassing event subscription filter.
     * Used for test deliveries.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'dispatchwebhookjob' => 'App\\Jobs\\DispatchWebhookJob',
              'webhookdelivery' => 'App\\Models\\WebhookDelivery',
              'webhookendpoint' => 'App\\Models\\WebhookEndpoint',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'endpoint',
               'type' => 'App\\Models\\WebhookEndpoint',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'eventType',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'payload',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'generateSecret',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Generate a cryptographically secure webhook secret.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'dispatchwebhookjob' => 'App\\Jobs\\DispatchWebhookJob',
              'webhookdelivery' => 'App\\Models\\WebhookDelivery',
              'webhookendpoint' => 'App\\Models\\WebhookEndpoint',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'sign',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Sign a payload with HMAC-SHA256.
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'dispatchwebhookjob' => 'App\\Jobs\\DispatchWebhookJob',
              'webhookdelivery' => 'App\\Models\\WebhookDelivery',
              'webhookendpoint' => 'App\\Models\\WebhookEndpoint',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'payload',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'secret',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/Users/sood/dev/heatware/laravel-react-starter/app/Support/CsvExport.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Support\\CsvExport',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @param  array<string, string|callable>  $columns  Map of \'Display Name\' => \'column_key\' or callable
     */',
             'namespace' => 'App\\Support',
             'uses' => 
            array (
              'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
              'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'columns',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'filename',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'static',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'filename',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getFilename',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'delimiter',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'static',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'delimiter',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'fromQuery',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => 'Illuminate\\Database\\Eloquent\\Builder',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'fromCollection',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'items',
               'type' => 'iterable',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'writeToStream',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Write CSV data to a stream resource (used by both streaming and tests).
     *
     * @param  resource  $stream
     */',
             'namespace' => 'App\\Support',
             'uses' => 
            array (
              'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
              'streamedresponse' => 'Symfony\\Component\\HttpFoundation\\StreamedResponse',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'items',
               'type' => 'iterable',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'stream',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
); },
];
