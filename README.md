# Jumpitt
API Rest para consultar por sugerencias de Ciudades, ordenadas segun la cercanía a la Latitud y Longitud entregadas.

Este proyecto fue realizado utilizando el micro-framework **Lumen**, creado por **Laravel**.

### Endpoint
- suggestions: retorna un JSON con los resultados de la busqueda.

### Parametros
- q: Parte del nombre de las ciudades a buscar
- latitude (opcional): Latitud de referencía
- longitude (opcional): Longitud de referencia

### Levantar Ambiente
Descargar el proyecto e ingresar mediante la consola a la carpeta de este, una vez adentro ejecutar el siguiente comando:

```
php -S localhost:8000 -t public
```
### Ejemplo de utilización
GET http://localhost:8000/suggestions?q=londo&latitude=42.70011&longitude=-81.4163
