# AcidORM

## config.neon
```php

	mapperManager: AcidORM\Managers\MapperManager
	
	persistorManager: 
		class: AcidORM\Managers\PersistorManager
		setup:
			- setMapperManager(@mapperManager)
			- setDb(@dibi.connection)

	facadeManager: 
		class: AcidORM\Managers\FacadeManager
		setup:
			- setPersistorManager(@persistorManager)
			- setMapperManager(@mapperManager)

	gridManager: AcidORM\Managers\GridManager
```