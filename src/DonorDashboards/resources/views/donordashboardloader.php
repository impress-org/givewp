<?php

$primaryColor = ! empty( $accentColor ) ? $accentColor : '#28C77B';
$primaryColor = trim( $accentColor, '#' );

$loaderBG = "&quot;data:image/svg+xml;charset=utf8,%3C?xml version='1.0' encoding='utf-8'?%3E%3C!-- Generator: Adobe Illustrator 24.1.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 349 348' style='enable-background:new 0 0 349 348;' xml:space='preserve'%3E%3Cstyle type='text/css'%3E .st0{fill:%23{$primaryColor};} %3C/style%3E%3Cpath class='st0' d='M25.1,204.57c-13.38,0-24.47-10.6-24.97-24.08C0.04,178.09,0,175.97,0,174C0,77.78,78.28-0.5,174.5-0.5 c13.81,0,25,11.19,25,25s-11.19,25-25,25C105.85,49.5,50,105.35,50,174c0,1.37,0.03,2.85,0.1,4.65c0.51,13.8-10.27,25.39-24.07,25.9 C25.72,204.56,25.41,204.57,25.1,204.57z'/%3E%3Cpath class='st0' d='M174.5,348.5c-13.81,0-25-11.19-25-25c0-13.81,11.19-25,25-25c68.65,0,124.5-55.85,124.5-124.5 c0-1.38-0.03-2.85-0.1-4.65c-0.51-13.8,10.26-25.4,24.06-25.91c13.83-0.53,25.4,10.26,25.91,24.06c0.09,2.39,0.13,4.51,0.13,6.49 C349,270.22,270.72,348.5,174.5,348.5z'/%3E%3C/svg%3E&quot;";

?>

<div class="iframe-loader" style="width: 100%; height: 100%; min-height: 790px; position: absolute; top: 0; left: 0; pointer-events: none;">
	<div style="
		background: white; 
		width: calc(90% - 12px); 
		height: 100%; 
		max-width: 920px; 
		margin: 8px auto; 
		border-radius: 8px; 
		display: flex; 
		align-items: center; 
		justify-content: center;
		-webkit-box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.14),
			0 3px 1px -2px rgba(0, 0, 0, 0.2),
			0 1px 5px 0 rgba(0, 0, 0, 0.12);
		box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.14),
			0 3px 1px -2px rgba(0, 0, 0, 0.2),
			0 1px 5px 0 rgba(0, 0, 0, 0.12);
		">
		<div style="
			pointer-events: none;
			height: 90px;
			width: 90px;
			background-image: url(<?php echo $loaderBG; ?>);
			animation: spin 0.6s linear infinite;
			"></div>
	</div>
</div>
