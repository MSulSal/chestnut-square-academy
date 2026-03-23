param(
  [string]$InputPath = "docs/Colorful handprint artwork on canvas.png",
  [string]$OutputPath = "app/public/wp-content/themes/hello-elementor-csa-site/assets/images/cursor-handprint.png",
  [int]$OutputSize = 48
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

Add-Type -AssemblyName System.Drawing

$srcPath = (Resolve-Path $InputPath).Path
$outPath = $ExecutionContext.SessionState.Path.GetUnresolvedProviderPathFromPSPath($OutputPath)
$outDir = Split-Path $outPath -Parent
if (-not (Test-Path $outDir)) {
  New-Item -Path $outDir -ItemType Directory -Force | Out-Null
}

$src = [System.Drawing.Bitmap]::new($srcPath)
$masked = [System.Drawing.Bitmap]::new($src.Width, $src.Height, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)

try {
  # 1) Knock out the checker/white canvas using low-saturation + bright threshold.
  for ($y = 0; $y -lt $src.Height; $y++) {
    for ($x = 0; $x -lt $src.Width; $x++) {
      $c = $src.GetPixel($x, $y)
      $max = [Math]::Max($c.R, [Math]::Max($c.G, $c.B))
      $min = [Math]::Min($c.R, [Math]::Min($c.G, $c.B))
      $delta = $max - $min
      $lum = [int](($c.R + $c.G + $c.B) / 3)

      $isBackground = ($delta -lt 22 -and $lum -gt 118) -or ($lum -gt 246)
      if ($isBackground) {
        $masked.SetPixel($x, $y, [System.Drawing.Color]::FromArgb(0, 0, 0, 0))
      } else {
        $masked.SetPixel($x, $y, [System.Drawing.Color]::FromArgb(255, $c.R, $c.G, $c.B))
      }
    }
  }

  # 2) Find non-transparent bounds.
  $minX = $masked.Width
  $minY = $masked.Height
  $maxX = -1
  $maxY = -1

  for ($y = 0; $y -lt $masked.Height; $y++) {
    for ($x = 0; $x -lt $masked.Width; $x++) {
      if ($masked.GetPixel($x, $y).A -gt 0) {
        if ($x -lt $minX) { $minX = $x }
        if ($x -gt $maxX) { $maxX = $x }
        if ($y -lt $minY) { $minY = $y }
        if ($y -gt $maxY) { $maxY = $y }
      }
    }
  }

  if ($maxX -lt 0 -or $maxY -lt 0) {
    throw "No foreground pixels found in cursor source."
  }

  $pad = 4
  $cropX = [Math]::Max(0, $minX - $pad)
  $cropY = [Math]::Max(0, $minY - $pad)
  $cropW = [Math]::Min($masked.Width - $cropX, ($maxX - $minX + 1) + ($pad * 2))
  $cropH = [Math]::Min($masked.Height - $cropY, ($maxY - $minY + 1) + ($pad * 2))

  $final = [System.Drawing.Bitmap]::new($OutputSize, $OutputSize, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
  $g = [System.Drawing.Graphics]::FromImage($final)
  try {
    $g.Clear([System.Drawing.Color]::Transparent)
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    $g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
    $g.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality

    $contentSize = $OutputSize - 4
    $scale = [Math]::Min($contentSize / [double]$cropW, $contentSize / [double]$cropH)
    $drawW = [int][Math]::Round($cropW * $scale)
    $drawH = [int][Math]::Round($cropH * $scale)
    $drawX = [int][Math]::Floor(($OutputSize - $drawW) / 2.0)
    $drawY = [int][Math]::Floor(($OutputSize - $drawH) / 2.0)

    $destRect = [System.Drawing.Rectangle]::new($drawX, $drawY, $drawW, $drawH)
    $srcRect = [System.Drawing.Rectangle]::new($cropX, $cropY, $cropW, $cropH)
    $g.DrawImage($masked, $destRect, $srcRect, [System.Drawing.GraphicsUnit]::Pixel)
  } finally {
    $g.Dispose()
  }

  # 3) Cleanup: remove any light fringe introduced by interpolation.
  for ($y = 0; $y -lt $final.Height; $y++) {
    for ($x = 0; $x -lt $final.Width; $x++) {
      $c = $final.GetPixel($x, $y)
      $max = [Math]::Max($c.R, [Math]::Max($c.G, $c.B))
      $min = [Math]::Min($c.R, [Math]::Min($c.G, $c.B))
      $delta = $max - $min
      if ($c.A -lt 245 -and $delta -lt 18 -and $c.R -gt 220 -and $c.G -gt 220 -and $c.B -gt 220) {
        $final.SetPixel($x, $y, [System.Drawing.Color]::FromArgb(0, 0, 0, 0))
      }
    }
  }

  if (Test-Path $outPath) {
    Remove-Item $outPath -Force
  }
  $final.Save($outPath, [System.Drawing.Imaging.ImageFormat]::Png)
  $final.Dispose()

  Write-Output ("Built cursor: {0}" -f $outPath)
} finally {
  $masked.Dispose()
  $src.Dispose()
}


