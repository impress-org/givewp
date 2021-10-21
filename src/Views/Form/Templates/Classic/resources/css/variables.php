:root {
	--give-primary-color: <?= $primaryColor ?>;
	--give-header-background-image: url("<?= $headerBackgroundImage ?>");
	--give-header-background-tint: <?= "{$primaryColor}BF" /* BF = 75% of 255 as a hexadecimal (rounded) */ ?>;
}
