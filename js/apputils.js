/*
	global object:  apputils = new clsAppUtils();

	provide general services to application
*/


//define public interface
var clsAppUtils = function() {
	this.name = 'apputils';
	this.prtctd = new clsAppUtils_protected();

	this.tableGen = function(aparams) {
		var
			pd =	this.prtctd,
			params = pd.ExtractParams({ caption: '',
							 										data: 	 [],
							 										ctrlPrefix: '',
							 										colHeads: null,
							 										sql: ''
																},
																aparams
															);
		if(	params.ctrlPrefix === '') params.ctrlPrefix = Math.round( Math.random() *1000);
		if(params.sql != '' ) {
				apputils[params.ctrlPrefix+'SQL'] = params.sql;
				//alert(apputils[params.ctrlPrefix+'SQL']);
		}
		var	stmp = "<div id='"+params.ctrlPrefix+"DivParent'>"
							 + "<div class='dataTableHeader'>"
								 + "<span class=''>"+params.caption+"</span>"
								 + "<span class=dataTableIcons>"
								 + (params.sql === '' ? '' : "<span href='#' onclick='apputils.showSQL(\""+params.ctrlPrefix+"\");' title='View SQL command'>SQL</span>")
							 + "</span></div>"
						 + "<div class='toggled'>",
				tb = jQuery.isArray(params.data)
					 ? pd.ArrayToTable(params.data, 'dataTable', params.ctrlPrefix, params.colHeads, true)
					 : params.data
					 ;
		return stmp
					+ tb
					+ '</div></div>';
		;
	}
	this.showSQL = function(ctrlPrefix) {
		//this is needed because alert(params.sql) fails
		alert(apputils[ctrlPrefix+'SQL']);
	}
}

//define protected interface
var clsAppUtils_protected = function() {
	this.ExtractParams = function(defaultparams, aparams) {
		var obj = {};
		if(aparams === undefined) return defaultparams;
		for(var x in defaultparams) {
			obj[x] = (typeof aparams[x] === 'undefined')
							? defaultparams[x]
							: aparams[x];
		}
		return obj;
	}
	this.ArrayToTable = function(arr, TableClass, IDPrefix, colHeaders, AIncludeCol, ATDCallBackFunc)	{
		if(typeof(ATDCallBackFunc) !== 'function')
			ATDCallBackFunc = null; //called like this:  ATDCallBackFunc(rowIdx, tdIdx, TD);

		var maxRows = arr.length;
		var maxCols = (typeof arr[maxRows -1] == 'object' ? arr[maxRows -1].length : 0);

		var table = "<table id='"+IDPrefix+"Table' class='"+TableClass+" sortable'>";

		if(typeof(colHeaders) == 'object') {
			table += "<thead><tr>";
			for(var col=0; col < maxCols; col++) {
				table += "<th id='"+IDPrefix+'_th'+col+"'>"
								+colHeaders[col]
								+"</th>";
			}
			table += "</tr></thead>";
		}
		table += "<tbody>";

		for(var idx = 0; idx < maxRows; idx++) {
			table += "<tr id="+IDPrefix+'_tr'+idx+">";
			artmp = arr[idx];
			for(var itmp = 0; itmp < artmp.length; itmp++) {
				table += "<td id="+IDPrefix+'_td'+itmp+" class='td"+itmp+"'>"
							 +	artmp[itmp]
							 + "</td>"
							 ;
			}
			table += "</tr>";
		}
		table += "</tbody></table>";
		return table;
	}

	this.MembersShow = function(obj) {
		var stmp = 'Object Properties\n'+this.MembersShowStr(obj);
		alert(stmp);
	}
	this.MembersShowStr = function(obj, prefix) {
		var stmp = '';

		if(prefix == undefined)
			prefix = ''

		jQuery.each(obj, function(name, value) {
			var typ = typeof(value);
			if(typ === 'object') {
				if(value === null)
					stmp += prefix+ name +' (NULL Object): ' + value+'\n';
				else
				if(jQuery.isArray( value ))
					stmp += prefix+ name +' (Array '+value.length+' rows):\n' + apputils.prtctd.MembersShowStr(value, prefix+'-')+'\n';
				else
					stmp += prefix+ name +' (Object): \n' + apputils.prtctd.MembersShowStr(value, prefix+'-')+'\n';
			} else
				stmp += prefix+ name +' ('+typ+ "): " + value+'\n';
		})

		return stmp;
	}
}

//global object
apputils = new clsAppUtils();

