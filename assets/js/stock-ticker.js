function wpau_stock_ticker_setup(symbols,id,show) {
	// prepare symbols
	symbols = symbols.replace(/\s/, ''); // strip all spaces
	var sym_arr = symbols.split(','); // split to array
	symbols = '"' + sym_arr.join('","') + '"'; // join to string

	var yql_sql = 'select Name, Symbol, LastTradePriceOnly, Change, ChangeinPercent, Volume from yahoo.finance.quotes where symbol in ('+symbols+')';
	var yql = '//query.yahooapis.com/v1/public/yql?q=' + encodeURI(yql_sql);
	yql += '&format=json';
	yql += '&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys';
	yql += '&diagnostics=false';
	yql += '&callback=?';

	jQuery.ajaxSetup({
		"error":function() {   
		jQuery("ul#"+id).html("<li class='init'>Sorry, currently we can't get stock quotes.</li>").webTicker();
	}});
	jQuery.getJSON(yql, function(data) {
		if ( data.query.results.quote != null ) {
			var items = [];
			jQuery.each(data.query.results.quote, function(key, val) {
				var price = val.LastTradePriceOnly;
				var change = val.Change;
				if ( change < 0 ) { chclass = "minus"; }
				else if ( change > 0 ) { chclass = "plus"; }
				else { chclass = "zero"; change = "0.00"; }

				if ( show == "name" ) {	company_show = val.Name; }
				else { company_show = val.Symbol; }
				items.push('<li class="'+chclass+'"><a href="http://finance.yahoo.com/q?s='+val.Symbol+'" target="_blank" title="'+val.Name+' (Vol: '+val.Volume+'; Ch: '+val.ChangeinPercent+')">'+company_show+' '+price+' '+change+'</a></li>');
			});
			jQuery("ul#"+id).html(items.join('')).webTicker();
		}

	});
}