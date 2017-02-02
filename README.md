# Acidclick\AcidORM

## config.neon
```php

	services:		

		acidORM:
			class: Acidclick\AcidORM
			setup:
				- setDb(@dibi.connection)
				- setCacheProvider(@cacheProvider)
				- setParameters(['appDir' = %appDir%])
				- startup()	
```