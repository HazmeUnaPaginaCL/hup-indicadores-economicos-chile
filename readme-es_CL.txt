### ─────────────────────────────────────────────────────────────────────────
### TRADUCCIÓN AL ESPAÑOL (es_CL) — ARCHIVO DE REFERENCIA, NO SE DISTRIBUYE
###
### WordPress.org NO lee este archivo. El readme oficial es readme.txt y debe
### permanecer en inglés (requisito del repositorio desde julio 2025).
###
### Para publicar la ficha en español, traduce las cadenas de readme.txt en:
###   https://translate.wordpress.org/projects/wp-plugins/hup-indicadores-economicos-chile/
### Usa el texto de abajo como fuente al completar esas traducciones.
###
### Este archivo está excluido del paquete vía .distignore.
### ─────────────────────────────────────────────────────────────────────────

=== HUP - Indicadores Económicos (Chile) ===
Contributors: hazmeunapagina
Tags: chile, currency, exchange rate, uf, economy
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Muestra los indicadores económicos de Chile (UF, UTM, Dólar, Euro y más) en tiempo real mediante un shortcode configurable. Sin dependencias externas.

== Description ==

**Los datos se obtienen en tiempo real desde la API pública de mindicador.cl** — un servicio gratuito que expone los indicadores económicos oficiales de Chile (con fuente en el Banco Central y otras entidades). No requiere registro ni clave de API.

HUP - Indicadores Económicos (Chile) consulta automáticamente la API de mindicador.cl y muestra los valores a través del shortcode `[hupind_indicadores]`, listo para usar en headers, footers, sidebars, Bricks Builder, Elementor o cualquier zona de tu sitio.

Este proyecto es independiente y no está afiliado a ninguna entidad gubernamental. Los datos se obtienen desde la API pública de mindicador.cl (https://mindicador.cl). El plugin solo recibe datos; nunca envía información al servicio.

**Características principales:**

* Datos en tiempo real desde la API pública de mindicador.cl (sin registro ni clave de API)
* 12 indicadores: UF, UTM, Dólar, Euro, IPC, IVP, IMACEC, TPM, Libra de Cobre, Tasa de Desempleo, Bitcoin y Dólar Intercambio
* Dólar Aduanero para importaciones/exportaciones — derivado del dólar observado (el valor del penúltimo día hábil del mes anterior, la misma regla que aplica el Servicio Nacional de Aduanas)
* Shortcode `[hupind_indicadores]` con múltiples parámetros configurables
* Panel de administración en **Herramientas** — selección de indicadores y generador de shortcode en una sola pantalla
* Formato automático según el tipo de indicador: `$` (CLP), `US$` (USD) o `%` (porcentaje)
* Variación respecto al día anterior: `%` para precios/divisas, puntos porcentuales (`pp`) para tasas e índices
* Flechas ▲ verde / ▼ roja para variación positiva/negativa
* Layout horizontal o vertical, separador configurable
* Selector de formato de fecha (corto, largo, numérico)
* Formato numérico chileno (coma decimal, punto de miles)
* Caché configurable con Transients de WordPress (1h / 6h / 12h / 24h)
* Respaldo automático al último valor exitoso si la API de mindicador.cl no responde
* Sin CSS en el frontend — estilízalo con clases BEM desde Bricks o Elementor
* Sin dependencias de WooCommerce ni de otros plugins externos

**Ejemplo de shortcode:**

`[hupind_indicadores show="uf,dolar,euro" layout="horizontal" show_variation="true" show_date="true"]`

**Clases BEM disponibles para estilizar:**

* `.hupind-indicadores` — contenedor principal
* `.hupind-indicadores__item` — cada indicador
* `.hupind-indicadores__item--{id}` — modificador por indicador (--uf, --dolar, etc.)
* `.hupind-indicadores__label` — nombre del indicador
* `.hupind-indicadores__value` — valor formateado
* `.hupind-indicadores__variation` — variación (con modificadores --up / --down)
* `.hupind-indicadores__divider` — separador entre elementos
* `.hupind-indicadores__updated` — fecha de actualización
* `.hupind-indicadores--error` / `.hupind-indicadores__error` — aviso mostrado cuando aún no hay datos disponibles

== External services ==

Este plugin se conecta a la API pública de mindicador.cl para obtener los valores actuales de los indicadores económicos chilenos.

* Servicio: mindicador.cl — https://mindicador.cl
* Qué se envía: nada. El plugin solo realiza solicitudes de lectura (GET) para obtener los valores de los indicadores; no se transmite ningún dato personal ni del sitio.
* Cuándo: al renderizar el shortcode y cuando la caché ha expirado (la duración de la caché es configurable, 1 hora por defecto).
* Términos / privacidad: https://mindicador.cl

== Installation ==

1. Sube la carpeta `hup-indicadores-economicos-chile` al directorio `/wp-content/plugins/`.
2. Activa el plugin desde el menú **Plugins** de WordPress.
3. Ve a **Herramientas → HUP Indicadores** en el panel de administración.
4. Activa los indicadores que quieras mostrar y ajusta los decimales.
5. Copia el shortcode generado automáticamente y pégalo donde lo necesites.

== Frequently Asked Questions ==

= ¿Necesito WooCommerce u otro plugin para usar esto? =

No. El plugin es completamente independiente. Funciona sin WooCommerce ni ningún otro plugin externo.

= ¿Los datos son en tiempo real? =

Los datos se obtienen desde la API pública de mindicador.cl. La frecuencia de actualización depende de la duración de caché configurada (1 hora por defecto). Indicadores como la UF se actualizan diariamente.

= ¿Puedo estilizar el shortcode? =

Sí. El plugin no carga ningún CSS en el frontend a propósito. Usa las clases BEM documentadas en el acordeón "Clases CSS" del panel de administración para aplicar tus estilos desde Bricks Builder, Elementor o tu propia hoja de estilos. Los colores de variación (verde/rojo) se pueden cambiar definiendo las variables CSS `--hupind-color-up` y `--hupind-color-down` en `.hupind-indicadores`.

= ¿Qué pasa si la API de mindicador.cl no responde? =

El plugin mantiene un transient de respaldo permanente con el último valor exitoso. Una vez que al menos una solicitud haya tenido éxito, ese valor se sirve automáticamente si la API falla después, por lo que el frontend nunca queda vacío. Solo en una instalación nueva, antes de la primera solicitud exitosa y mientras la API no responde, no hay ningún valor que mostrar; en ese caso el shortcode muestra un aviso breve que puedes personalizar con el atributo `error_text` (usa `error_text=""` para no mostrar nada).

= ¿Cómo se calcula el Dólar Aduanero? =

Se calcula automáticamente a partir del dólar observado del Banco Central: toma el valor observado del penúltimo día hábil del mes anterior, que es la misma regla que aplica el Servicio Nacional de Aduanas para el mes en curso. El plugin no hace scraping de aduana.cl. Se activa desde el panel de administración, donde se muestra una nota explicativa al activarlo.

= ¿Puedo mostrar solo algunos indicadores? =

Sí, de dos formas: activándolos/desactivándolos desde el panel de administración, o usando el parámetro `show` directamente en el shortcode: `[hupind_indicadores show="uf,dolar,euro"]`.

= ¿Cuántos decimales se muestran? =

Configurable por indicador desde el panel de administración (0 a 4 decimales).

== Screenshots ==

1. Panel de administración — selección de indicadores con interruptores y configuración de decimales.
2. Opciones del shortcode con generador en vivo, formato de fecha y referencia de clases BEM en un acordeón.
3. Panel de caché con estadísticas de la última consulta y duración configurable.

== Changelog ==

= 1.1.1 =
* Se agrega un espacio entre el nombre del indicador, su valor y la variación (ej. "UF $38.123,45 ▲ 0,12%") para mejor legibilidad. Solo visual; sin cambios de comportamiento.

= 1.1.0 =
* Nuevo indicador: Dólar Aduanero, usado para la valoración de importaciones/exportaciones. Se deriva del dólar observado del Banco Central (el valor del penúltimo día hábil del mes anterior, la misma regla que usa el Servicio Nacional de Aduanas); no se obtiene por scraping de aduana.cl.
* El panel de administración muestra una nota explicativa al activar el Dólar Aduanero, para dejar claro cómo se obtiene el valor.

= 1.0.1 =
* Requisito mínimo de PHP bajado de 8.3 a 7.4 para mayor compatibilidad con hostings. Sin cambios funcionales; el comportamiento es idéntico en PHP 8.x.

= 1.0.0 =
* Primera publicación pública.
* 12 indicadores desde la API pública de mindicador.cl: UF, UTM, Dólar, Euro, IPC, IVP, IMACEC, TPM, Libra de Cobre, Tasa de Desempleo, Bitcoin, Dólar Intercambio.
* Shortcode `[hupind_indicadores]` con parámetros: show, layout, separator, class, show_date, show_variation, format, date_format, error_text.
* Panel de administración en Herramientas: selección de indicadores y generador de shortcode en vivo en una sola pantalla, más un panel de Caché.
* Formato automático según el tipo de indicador: $ (CLP), US$ (USD) o % (porcentaje); variación en % para precios/divisas y en puntos porcentuales (pp) para tasas e índices.
* Flechas ▲ verde / ▼ roja, con colores sobreescribibles mediante variables CSS.
* Layout horizontal o vertical, separador configurable y selector de formato de fecha.
* Formato numérico chileno (coma decimal, punto de miles).
* Frontend no bloqueante: los valores se sirven desde caché/respaldo y se actualizan en segundo plano vía WP-Cron, con respaldo automático si la API no está disponible.
* Aviso personalizable (`error_text`) mostrado solo en una instalación nueva cuando aún no hay ningún valor disponible y la API no responde.
* Sin CSS en el frontend — diseño BEM listo para estilizar. Compatible con Bricks Builder y Elementor.

== Upgrade Notice ==

= 1.1.1 =
Agrega espacio entre el nombre del indicador, el valor y la variación para mejor legibilidad. Solo visual.

= 1.1.0 =
Agrega el indicador Dólar Aduanero, derivado del dólar observado.

= 1.0.1 =
Baja el requisito mínimo de PHP a 7.4. Sin cambios funcionales.

= 1.0.0 =
Primera publicación pública.
