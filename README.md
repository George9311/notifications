# Back In Stock Notifications + Overlay
Smart WooCommerce extension that lets shoppers subscribe for **“back in stock”** alerts on out‑of‑stock products.  
Includes a one‑time overlay popup, a sleek on‑page form, and automated HTML e‑mails—no third‑party services required.

![PHP tested](https://img.shields.io/badge/PHP-tested-blue?logo=php) ![WordPress tested](https://img.shields.io/badge/WordPress-6.4%2B-blue?logo=wordpress) ![WooCommerce tested](https://img.shields.io/badge/WooCommerce-8.x-purple?logo=woocommerce)

---

## ✨ Features
|                             | Description |
|-----------------------------|-------------|
| **CPT for subscriptions**   | Custom Post Type (`backinstock_sub`) to store e‑mails & product IDs—fully manageable in WP‑Admin. |
| **Frontend shortcode**      | `[back_in_stock product_id="123"]` renders a responsive e‑mail capture form only when the product is *out of stock*. |
| **Overlay popup**           | Optional overlay shown *once every 55 days* on OOS product pages, driving higher opt‑ins. |
| **Auto notifications**      | When stock status flips to *instock*, all subscribers receive a branded HTML e‑mail and the records are auto‑cleared. |
| **Clean UI**                | Lightweight markup + inline SVG icons; inherits your theme styles. |
| **Developer‑friendly**      | 100 % PHP 8, no external dependencies, well‑commented hooks & filters. |

---

## ⚙️ Requirements
* WordPress 6.0+
* WooCommerce 7.0+
* PHP 8.0+

---

## 🚀 Installation
1. Download the latest release from GitHub or clone the repo:  
   ```bash
   git clone https://github.com/George9311/back-in-stock-notifications.git

   2. Upload the folder to /wp-content/plugins/ (or) install the ZIP via Plugins → Add New → Upload.

3. Activate Back In Stock Notifications + Overlay from the Plugins screen.

4. (Optional) Go to Settings → Permalinks → Save to flush rewrite rules.

// Inside a product template or shortcode
echo do_shortcode('[back_in_stock]');


🛠 Hooks & Filters
Hook	Type	Purpose
wp_mail_from_name	filter	Change “From” name (default: webiste.com).
wp_mail_from	filter	Change “From” e‑mail (default: notify@website.com).
woocommerce_product_set_stock_status	action	Core WooCommerce hook the plugin taps into to trigger e‑mails.

Feel free to add your own filters for templates, cookie duration, etc.


Let me know if you’d like extra badges, CI instructions, or screenshot captions!
Built with ❤️ by George 
https://www.linkedin.com/in/giorgian-ionut-terchila/
Need help extending the plugin? Open an issue or reach out on LinkedIn.
