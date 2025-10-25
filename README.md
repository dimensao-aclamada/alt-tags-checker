# 🖼️ Alt Tag Checker — WordPress Plugin

**Find and fix missing image alt tags across your WordPress site.**  
A fast, visual, and SEO-friendly scanner that crawls your pages to locate images missing alt attributes, helping you improve accessibility and search performance.

---

## 🚀 Features

✅ **Automatic Site Scan**
- Crawls your entire site (up to a configurable depth).
- Detects pages containing images with missing `alt` attributes.

✅ **Smart Results Dashboard**
- Clean, WordPress-native admin UI.
- Displays pages and thumbnails for all images missing alt tags.
- Direct links to edit images in the Media Library.

✅ **Crawl Depth Control**
- Choose how deep the plugin should follow internal links.

✅ **Result Persistence**
- Stores the last scan results and timestamp for quick reference.

✅ **Quick Management Tools**
- One-click **New Scan** and **Clear Scan Results** buttons.

✅ **Performance-Friendly**
- Lightweight crawler with URL normalization and loop-prevention.

---

## 🧭 Why Alt Tags Matter

- **Accessibility**: Screen readers use alt text to describe images to visually impaired users.  
- **SEO**: Search engines use alt text to understand image context and relevance.  
- **Performance**: Identifying missing tags early helps prevent accessibility issues before they affect rankings.

---

## 🛠️ How It Works

1. Go to **Admin → Alt Tag Checker**.  
2. Set your **crawl depth** (recommended: 2).  
3. Click **New Scan**.  
4. Review results:
   - Each page with missing alt tags appears in a list.
   - Images link directly to the Media Library for quick editing.

You can clear previous scan results anytime.

---

## 🧩 Example Output

![Example Screenshot](https://user-images.githubusercontent.com/example/alt-tag-checker-dashboard.png)

> Pages with missing alt tags are listed with thumbnails and links for easy correction.

---

## ⚙️ Installation

1. Download the plugin ZIP or clone this repository.  
2. Upload it to `/wp-content/plugins/`.  
3. Activate **Alt Tag Checker** via **Plugins → Installed Plugins**.  
4. Access it under **Alt Tag Checker** in your WordPress admin sidebar.

---

## 🔒 Security & Performance Notes

- Uses native `wp_remote_get()` for crawling.  
- Respects internal links only (no external crawling).  
- Enforces a maximum crawl depth of 5.  
- Results are stored in the `wp_options` table (`atc_last_scan_results`).  
- Safe to uninstall — no custom tables are created.

---

## 📦 Technical Overview

| Component | Description |
|------------|-------------|
| **Core Class** | `AltTagChecker` |
| **Option Key** | `atc_last_scan_results` |
| **Main Functions** | `crawl_site()`, `check_page()`, `get_page_links()`, `get_attachment_id()` |
| **Dependencies** | Uses `DOMDocument`, `wp_remote_get`, and standard WordPress APIs |
| **Compatibility** | Tested on WordPress 6.x and PHP 7.4–8.2 |

---

## 🧰 Developer Notes

- The crawler normalizes URLs (ignoring fragments and query strings) to prevent duplicates.  
- All output is properly escaped (`esc_url`, `esc_html`).  
- Inline styles are injected dynamically to keep the plugin lightweight.  
- Designed with extendability in mind — ideal for building a Pro version with:
  - Scheduled scans
  - CSV/PDF export
  - AI-generated alt text
  - API/webhook integrations

---

## ❤️ Support & Feedback

Found a bug? Have a feature idea?  
Open an issue on GitHub or contact the author via your WordPress dashboard.  
Every suggestion helps make this plugin better for everyone.

---

## 📜 License

Released under the [GPL-2.0+ License](https://www.gnu.org/licenses/gpl-2.0.html).

You are free to use, modify, and redistribute this plugin under the same license.

---

## 🌟 Coming Soon (Pro Version)

The upcoming **Alt Tag Checker PRO** will include:
- 🔁 Scheduled & incremental scans  
- 🧠 AI-powered alt text generation  
- 📊 CSV & PDF export reports  
- 🏷️ Media Library sync  
- 🔔 Email notifications  
- 🏢 White-label and multisite support  

*Stay tuned for release details.*

---

**Developed with care for accessibility, SEO, and cleaner WordPress content.**  
_“Because every image deserves a voice.”_
