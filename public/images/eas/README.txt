EA product images — folder structure per slug:

  images/eas/{ea-slug}/
    thumbnail.jpg          ← product card + hero (recommended: 800x600)
    gallery/
      01-overview.jpg      ← shown in product page gallery (section: overview)
      02-backtest.jpg
      03-live-signal.jpg
    content/
      setup-guide.jpg      ← referenced inline in description_md markdown
      performance.jpg
    set-review/
      01-setfile.jpg       ← shown in Set Review tab (section: set_file)

After placing files, add them to the product via artisan tinker:

  php artisan tinker

  $p = \App\Models\Product::where('slug', 'your-ea-slug')->firstOrFail();

  // Thumbnail
  $p->update(['thumbnail_url' => '/images/eas/your-ea-slug/thumbnail.jpg']);

  // Gallery images
  $ext = $p->extended ?? [];
  $ext['images'] = [
    ['url' => '/images/eas/hedgehog-mt5/gallery/01-overview.jpg',  'alt_text' => 'Overview',  'section' => 'overview'],
    ['url' => '/images/eas/hedgehog-mt5/gallery/02-backtest.jpg',  'alt_text' => 'Backtest',  'section' => 'overview'],
  ];
  $p->update(['extended' => $ext]);

  // Set review images
  $sr = $p->set_review_data ?? [];
  $sr['images'] = [
    ['url' => '/images/eas/your-ea-slug/set-review/01-setfile.jpg', 'alt_text' => 'Set file chart', 'section' => 'set_file'],
  ];
  $p->update(['set_review_data' => $sr]);

Inline content images — write directly in description_md markdown:
  ![Caption text](/images/eas/your-ea-slug/content/chart.jpg)



$p = \App\Models\Product::where('slug', 'hedgehog-mt5')->firstOrFail();
$ext = $p->extended;

// Insert image after the "Strategy & Logic" section heading
$ext['description_md'] = str_replace(
    "## Strategy & Logic",
    "## Strategy & Logic\n\n![Strategy diagram](/images/eas/hedgehog-mt5/content/strategy.png)",
    $ext['description_md']
);

$p->update(['extended' => $ext]);