=== HUP - Indicadores Económicos (Chile) ===
Contributors: hazmeunapagina
Tags: chile, currency, exchange rate, uf, economy
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show Chile's economic indicators (UF, UTM, Dollar, Euro and more) in real time via a configurable shortcode. No external dependencies.

== Description ==

**Data is fetched in real time from the public mindicador.cl API** — a free service that exposes official economic indicators for Chile (sourced from the Central Bank and other entities). No registration or API key is required.

HUP - Indicadores Económicos (Chile) queries the mindicador.cl API automatically and outputs the values through the `[hupind_indicadores]` shortcode, ready to use in headers, footers, sidebars, Bricks Builder, Elementor and any area of your site.

This project is independent and is not affiliated with any government entity. Data is obtained from the public mindicador.cl API (https://mindicador.cl). The plugin only receives data; it never sends any information to the service.

**Main features:**

* Real-time data from the public mindicador.cl API (no registration or API key)
* 12 indicators: UF, UTM, Dollar, Euro, IPC, IVP, IMACEC, TPM, Copper Pound, Unemployment Rate, Bitcoin and Exchange Dollar
* Customs Dollar (Dólar Aduanero) for imports/exports — derived from the observed dollar (the value of the second-to-last business day of the previous month, the same rule used by the National Customs Service)
* `[hupind_indicadores]` shortcode with multiple configurable parameters
* Admin screen under **Tools** — indicator selection and shortcode builder on a single page
* Automatic format per indicator type: `$` (CLP), `US$` (USD) or `%` (percentage)
* Change versus the previous day: `%` for prices/currencies, percentage points (`pp`) for rates and indices
* ▲ green / ▼ red arrows for positive/negative change
* Horizontal or vertical layout, configurable separator
* Date format selector (short, long, numeric)
* Chilean number format (comma decimal, period thousands separator)
* Configurable cache with WordPress Transients (1h / 6h / 12h / 24h)
* Automatic fallback to the last successful value if the mindicador.cl API is unavailable
* No CSS on the frontend — style it with BEM classes from Bricks or Elementor
* No WooCommerce or external plugin dependencies

**Example shortcode:**

`[hupind_indicadores show="uf,dolar,euro" layout="horizontal" show_variation="true" show_date="true"]`

**BEM classes available for styling:**

* `.hupind-indicadores` — main wrapper
* `.hupind-indicadores__item` — each indicator
* `.hupind-indicadores__item--{id}` — per-indicator modifier (--uf, --dolar, etc.)
* `.hupind-indicadores__label` — indicator name
* `.hupind-indicadores__value` — formatted value
* `.hupind-indicadores__variation` — change (with --up / --down modifiers)
* `.hupind-indicadores__divider` — separator between items
* `.hupind-indicadores__updated` — update date
* `.hupind-indicadores--error` / `.hupind-indicadores__error` — notice shown when no data is available yet

== External services ==

This plugin connects to the public mindicador.cl API to retrieve the current values of Chilean economic indicators.

* Service: mindicador.cl — https://mindicador.cl
* What is sent: nothing. The plugin only performs read (GET) requests to retrieve indicator values; no personal or site data is transmitted.
* When: when the shortcode is rendered and the cache has expired (cache duration is configurable, default 1 hour).
* Terms / privacy: https://mindicador.cl

== Installation ==

1. Upload the `hup-indicadores-economicos-chile` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin from the **Plugins** menu in WordPress.
3. Go to **Tools → HUP Indicadores** in the WordPress admin.
4. Enable the indicators you want to show and adjust the decimals.
5. Copy the automatically generated shortcode and paste it wherever you need it.

== Frequently Asked Questions ==

= Do I need WooCommerce or another plugin to use this? =

No. The plugin is fully independent. It works without WooCommerce or any other external plugin.

= Is the data in real time? =

Data is fetched from the public mindicador.cl API. The refresh frequency depends on the configured cache duration (1 hour by default). Indicators such as the UF are updated daily.

= Can I style the shortcode? =

Yes. The plugin does not load any CSS on the frontend on purpose. Use the BEM classes documented in the "CSS classes" accordion of the admin screen to apply your styles from Bricks Builder, Elementor or your own stylesheet. The change colors (green/red) can be changed by defining the CSS variables `--hupind-color-up` and `--hupind-color-down` on `.hupind-indicadores`.

= What happens if the mindicador.cl API does not respond? =

The plugin keeps a permanent fallback transient with the last successful value. Once at least one request has succeeded, that value is served automatically if the API later fails, so the frontend never goes blank. Only on a brand-new install, before the first successful request and while the API is unreachable, is there no value to show; in that case the shortcode outputs a short notice that you can customize with the `error_text` attribute (set `error_text=""` to output nothing).

= How is the Customs Dollar (Dólar Aduanero) calculated? =

It is computed automatically from the Central Bank's observed dollar: it takes the observed value of the second-to-last business day of the previous month, which is the same rule the National Customs Service applies for the current month. The plugin does not scrape aduana.cl. Enable it from the admin screen, where an explanatory note is shown when you turn it on.

= Can I show only some indicators? =

Yes, in two ways: enabling/disabling them from the admin screen, or using the `show` parameter directly in the shortcode: `[hupind_indicadores show="uf,dolar,euro"]`.

= How many decimals are shown? =

Configurable per indicator from the admin screen (0 to 4 decimals).

== Screenshots ==

1. Admin screen — indicator selection with toggles and decimal configuration.
2. Shortcode options with live builder, date format and BEM class reference in an accordion.
3. Cache screen with last fetch statistics and configurable duration.

== Changelog ==

= 1.1.1 =
* Added a space between the indicator name, its value and the change (e.g. "UF $38.123,45 ▲ 0,12%") for better readability. Visual only; no behavior changes.

= 1.1.0 =
* New indicator: Customs Dollar (Dólar Aduanero), used for import/export valuation. It is derived from the Central Bank's observed dollar (the value of the second-to-last business day of the previous month, the same rule used by the National Customs Service); it is not scraped from aduana.cl.
* The admin screen shows an explanatory note when the Customs Dollar is enabled, so it is clear how the value is obtained.

= 1.0.1 =
* Lowered the minimum PHP requirement from 8.3 to 7.4 for broader hosting compatibility. No functional changes; behavior is identical on PHP 8.x.

= 1.0.0 =
* First public release.
* 12 indicators from the public mindicador.cl API: UF, UTM, Dollar, Euro, IPC, IVP, IMACEC, TPM, Copper Pound, Unemployment Rate, Bitcoin, Exchange Dollar.
* `[hupind_indicadores]` shortcode with parameters: show, layout, separator, class, show_date, show_variation, format, date_format, error_text.
* Admin screen under Tools: indicator selection and live shortcode builder on a single page, plus a Cache screen.
* Automatic format per indicator type: $ (CLP), US$ (USD) or % (percentage); change in % for prices/currencies and in percentage points (pp) for rates and indices.
* ▲ green / ▼ red arrows, with colors overridable via CSS variables.
* Horizontal or vertical layout, configurable separator and date format selector.
* Chilean number format (comma decimal, period thousands separator).
* Non-blocking frontend: values are served from cache/fallback and refreshed in the background via WP-Cron, with automatic fallback if the API is unavailable.
* Customizable notice (`error_text`) shown only on a fresh install when no value is available yet and the API is unreachable.
* No CSS on the frontend — BEM design ready to style. Compatible with Bricks Builder and Elementor.

== Upgrade Notice ==

= 1.1.1 =
Adds spacing between the indicator name, value and change for better readability. Visual only.

= 1.1.0 =
Adds the Customs Dollar (Dólar Aduanero) indicator, derived from the observed dollar.

= 1.0.1 =
Lowers the minimum PHP requirement to 7.4. No functional changes.

= 1.0.0 =
First public release.
