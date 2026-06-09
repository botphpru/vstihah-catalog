<?php
$a = 1;
$arrLinks = [];
foreach($breadcrumbsArr as $key => $value) {
    $arrLinks[] = '{
      "@type": "ListItem",
      "position": '.$a.',
      "item":
      {
        "@id": "'.$value.'",
        "name": "'.$key.'"
      }
    }';
    $a++;
}
$breadcrumbJSON = '
<script type="application/ld+json">
{
  "@context": "http://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement":
  [
    '.implode(",\n", $arrLinks).'
  ]
}
</script>
';
echo $breadcrumbJSON;

