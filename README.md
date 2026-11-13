# Earth Orchestra

NASA Earth data you can see, hear and play with. Six GEOS-FP / GEOS-CF
visualisations, each paired with its own piece of music. Moving across the map
changes the volume according to the data underneath.

- **Backend:** Laravel 12, MySQL / MariaDB
- **Frontend:** Blade, hand-written CSS, plain JavaScript modules (Web Audio API,
  Canvas, Speech Synthesis), bundled with Vite. There's no frontend framework.

## Running it locally

Requirements: PHP 8.2+, Composer, Node 18+, MySQL or MariaDB (XAMPP works).

```bash
composer install
npm install
cp .env.example .env          # DB_DATABASE=earth_orchestra, root / no password by default
php artisan key:generate

# create the database once
mysql -u root -e "CREATE DATABASE earth_orchestra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

php artisan migrate --seed
npm run build                 # or `npm run dev` while editing CSS/JS
php artisan serve
```

Then open http://127.0.0.1:8000.

Run the tests with `php artisan test`. They use an in-memory SQLite database,
so they never touch MySQL.

## How it fits together

| Path | What it does |
| --- | --- |
| `/` | Title, overture, how-to, the six movements, and the full orchestra |
| `/movements/{slug}` | "Learn more" notes for one movement, with linked citations |
| `/about`, `/credits` | Team pages |
| `/arrangements/{code}` | Home page with a saved orchestra mix preloaded |
| `POST /api/arrangements` | Saves a mix (CSRF-protected, rate limited) |
| `GET /api/arrangements/{code}` | Mix as JSON |

**Content lives in the database.** The first seed loads it from
`database/seeders/content/visualisations.json` and `pages.json`. After that,
edit everything in the admin panel. Re-running the seeder only adds rows that
are missing, so it never overwrites edits made in admin. To reset the content
back to the JSON, run:

```bash
CONTENT_SEED_OVERWRITE=true php artisan db:seed --class=ContentSeeder
```

## Admin panel

Open `/admin`. The first `php artisan migrate --seed` creates an administrator
using these `.env` values:

```
ADMIN_EMAIL=admin@earthorchestra.test
ADMIN_PASSWORD=change-me-now
```

Change that password right after signing in (My profile). To add more accounts,
use **Admins & editors** in the panel, or run `php artisan admin:create`.

| Section | What you can do |
| --- | --- |
| Dashboard | Totals, a 7-day chart of saved arrangements and shared-link listens, system health, recent activity |
| Movements | Edit titles, intro, learn-more article, citations, legend JSON (with live preview), dates, accent colour; replace poster/video/audio |
| Pages | Edit the home page copy, About us and Credits (team roles, tools, AI note) |
| Arrangements | Search, sort, open or delete saved mixes |
| Admins & editors | Administrators only: add, edit and remove accounts |

Roles: **editors** manage content. **Administrators** can also manage people.
Logins are rate limited, and the panel has light and dark themes and a
collapsible sidebar.

If you replace videos through the panel, PHP's `upload_max_filesize` and
`post_max_size` need to be larger than the file (for example 256M).

### Colour → sound

Each visualisation has a `legend` in its JSON row:

- `stops`: the colour bar from low to high, drawn evenly spaced.
- `aliases`: extra colours seen in the footage that map to a known value.
- `neutral`: colours that mean "no data", such as land masks or borders.
- `ticks`: the labels printed under the bar.

In the browser (`resources/js/lib/legend.js`), each sampled pixel is matched to
the nearest legend colour. Its position along the bar (0 = quiet, 1 = loud) sets
the volume. The stop colours were sampled from the videos themselves, and the
values follow the NASA SVS colour bars. If NASA publishes a different range for
a dataset, update `stops` and reseed.

### Interaction modes

- **Cursor:** hover or drag over the map to hear the value under the pointer.
  Double-click (or double-tap) to lock that volume, and double-click the dot to
  release it. Arrow keys move the probe; Enter locks.
- **Slider:** drag the vertical line. The volume follows the average value along
  that line as the video plays.
- **Manual volume:** overrides both modes while it's ticked.

The audio file is the master clock, and the video is kept in step with it. Only
one thing plays at a time: starting a movement pauses the orchestra, and the
other way round.

## Media

`public/media` holds web-optimised copies of the original assets:

- Videos are re-encoded to H.264 with `faststart`, at 1600 px wide (1440 px for
  wind and precipitation). This takes the set from 245 MB to about 80 MB.
- Each video has a WebP poster.
- The illustrations are converted to WebP.
- The three clouds are cut out as separate layers so they can drift
  independently.

The original files in the project root are untouched.

## Deploying

Point the web server at `public/`, then run:

```bash
php artisan migrate --force --seed
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Use `APP_ENV=production` and `APP_DEBUG=false`. Serve `public/media` with long
cache headers, since the files never change in place.
