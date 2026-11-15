<?php
	$svg_start = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
	$svg_end = '</svg>';

	$icon_dashboard = $svg_start . '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>' . $svg_end;
	$icon_route = $svg_start . '<path d="M20 10c0 5-5.5 10.2-7.4 11.8a1 1 0 0 1-1.2 0C9.5 20.2 4 15 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>' . $svg_end;
	$icon_vehicle = $svg_start . '<path d="M8 6v6"/><path d="M15 6v6"/><path d="M2 12h19.6"/><path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2s-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3"/><circle cx="7" cy="18" r="2"/><path d="M9 18h5"/><circle cx="16" cy="18" r="2"/>' . $svg_end;
	$icon_operator = $svg_start . '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/><path d="M16 3.1a4 4 0 0 1 0 7.8"/>' . $svg_end;
	$icon_search = $svg_start . '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>' . $svg_end;
	$icon_view = $svg_start . '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>' . $svg_end;
	$icon_edit = $svg_start . '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>' . $svg_end;
	$icon_delete = $svg_start . '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>' . $svg_end;
	$icon_check = $svg_start . '<path d="M20 6 9 17l-5-5"/>' . $svg_end;
	$icon_clock = $svg_start . '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>' . $svg_end;
	$icon_alert = $svg_start . '<circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>' . $svg_end;
	$icon_minus = $svg_start . '<path d="M5 12h14"/>' . $svg_end;
	$icon_map = $svg_start . '<path d="M14.1 5.6a2 2 0 0 0 1.8 0l3.7-1.9A1 1 0 0 1 21 4.6v12.8a1 1 0 0 1-.6.9l-4.5 2.3a2 2 0 0 1-1.8 0l-4.2-2.1a2 2 0 0 0-1.8 0l-3.7 1.8A1 1 0 0 1 3 19.4V6.6a1 1 0 0 1 .6-.9l4.5-2.3a2 2 0 0 1 1.8 0z"/><path d="M15 5.8v15"/><path d="M9 3.2v15"/>' . $svg_end;
	$icon_assign = $svg_start . '<path d="M8 3 4 7l4 4"/><path d="M4 7h16"/><path d="m16 21 4-4-4-4"/><path d="M20 17H4"/>' . $svg_end;
	$icon_document = $svg_start . '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>' . $svg_end;
	$icon_inspection = $svg_start . '<rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>' . $svg_end;
	$icon_group = $svg_start . '<path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/>' . $svg_end;
	$icon_renew = $svg_start . '<path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/>' . $svg_end;
	$icon_shield = $svg_start . '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>' . $svg_end;
	$icon_back = $svg_start . '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>' . $svg_end;
?>
