var MODE_FLAGS = { transit: "r", driving: "d" };

function routeEmbedUrl(from, stops, to, mode) {
	var target = to;
	if (mode == "driving" && stops.length > 0) {
		target = stops.join(" to:") + " to:" + to;
	}
	return "https://maps.google.com/maps?saddr=" + encodeURIComponent(from) +
		"&daddr=" + encodeURIComponent(target) + "&dirflg=" + (MODE_FLAGS[mode] || "d") + "&output=embed";
}

function routeLink(from, stops, to, mode) {
	var url = "https://www.google.com/maps/dir/?api=1&origin=" + encodeURIComponent(from) +
		"&destination=" + encodeURIComponent(to) + "&travelmode=" + mode;
	if (mode == "driving" && stops.length > 0) {
		url += "&waypoints=" + encodeURIComponent(stops.join("|"));
	}
	return url;
}

function readStops(textBox) {
	var lines = textBox.value.split("\n");
	var stops = [];
	for (var i = 0; i < lines.length; i++) {
		var line = lines[i].trim();
		if (line != "") {
			stops.push(line);
		}
	}
	return stops;
}
