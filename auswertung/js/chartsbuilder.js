/*
 * Laden von Json von der DB, und erstellen eines Diagramm
 * 
 */

function filter_bau() {
    i = 1
}

function wertetabelle() {
    if (!wertetabellenanzeige) {
        $('#Ausgabe').show();
        $('#btn_wertetabelle').text("Wertetabelle verstecken")
        wertetabellenanzeige = true;
    } else {
        $('#Ausgabe').hide();
        wertetabellenanzeige = false;
        $('#btn_wertetabelle').text("zeige Wertetabelle")
    }
}

function kill_chart() {
    barGraph.destroy();
}


function loadchart(chart) {
    $.post("./afterload.php", data = {
            "output": "json",
            "chart": chart,
            "passwort": $('#passwort').val(),
            "APP":'show_chart'
        }, function (datas) {
            if (datas != "Error") {
                spalten = datas;
                $('#passwortabfrage').hide();
                $('#myform').show();
                $('#btn_wertetabelle').show();
                var htmlstring = '<select id="spaltenauswahl" multiple size="8">';
                for (i in spalten) {
                    htmlstring += '<option value="' + spalten[i] + '">' + spalten[i] + '</option>';
                }
                htmlstring += "</select>";
                if(chart=="month"){
		$(".filter").hide();
		}else{
		$(".filter").show();
		}
		$("#spalten").html(htmlstring);
            }});
    }

    $(document).ready(function () {
        wertetabellenanzeige = false;
        $('#Ausgabe').hide();
        $('#myform').hide();
        $('#btn_wertetabelle').hide()
        $('#passwortabfrage').submit(function (event) {
            event.preventDefault();
            wert = $('#passwort').val();

            $.post("./afterload.php", data = {
                "passwort": wert,
                "output": "json",
                "APP":'show_chart',
                "chart": "day"
            }, function (datas) {
                if (datas != "Error") {
                    spalten = datas;
                    $('#passwortabfrage').hide();
                    $('#myform').show();
                    $('#btn_wertetabelle').show();
                    var htmlstring = '<select id="spaltenauswahl" multiple size="8">';
                    for (i in spalten) {
                        htmlstring += '<option value="' + spalten[i] + '">' + spalten[i] + '</option>';
                    }
                    htmlstring += "</select>";
                    $("#spalten").html(htmlstring);

                } else {
                    $('#passwortf').append("falsches Passwort <br>");
                }
            })


        });

        var _table_ = document.createElement('table'),
            _tr_ = document.createElement('tr'),
            _th_ = document.createElement('th'),
            _td_ = document.createElement('td');

        // Builds the HTML Table out of myList json data from Ivy restful service.
        function buildHtmlTable(arr) {
            var table = _table_.cloneNode(false),
                columns = addAllColumnHeaders(arr, table);
            for (var i = 0, maxi = arr.length; i < maxi; ++i) {
                var tr = _tr_.cloneNode(false);
                for (var j = 0, maxj = columns.length; j < maxj; ++j) {
                    var td = _td_.cloneNode(false);
                    cellValue = arr[i][columns[j]];
                    td.appendChild(document.createTextNode(arr[i][columns[j]] || ''));
                    tr.appendChild(td);
                }
                table.appendChild(tr);
            }
            return table;
        }

        // Adds a header row to the table and returns the set of columns.
        // Need to do union of keys from all records as some records may not contain
        // all records
        function addAllColumnHeaders(arr, table) {
            var columnSet = [],
                tr = _tr_.cloneNode(false);
            for (var i = 0, l = arr.length; i < l; i++) {
                for (var key in arr[i]) {
                    if (arr[i].hasOwnProperty(key) && columnSet.indexOf(key) === -1) {
                        columnSet.push(key);
                        var th = _th_.cloneNode(false);
                        th.appendChild(document.createTextNode(key));
                        tr.appendChild(th);
                    }
                }
            }
            table.appendChild(tr);
            return columnSet;
        }
        $('#charttyoe').change(function() {
            loadchart( $('#charttyoe').val());
          });

        $('#myform').submit(function (event) {
            event.preventDefault();

            $.ajax({
                url: "./afterload.php",
                method: "POST",
                data: {
                    'APP': "show_chart",
                    'beginn': $('#beginn').val(),
                    'ende': $('#ende').val(),
                    "passwort": String(wert),
                    'filter1_min': $('#filter1_min').val(),
                    'chart': $('#charttyoe').val(),
                    'filter1_max': $('#filter1_max').val()
                },
                success: function (obj) {

                    werte = obj;

                    table = buildHtmlTable(werte);
                    $('#Ausgabe').html(table);

                    y = 0;
                    z = 0;

                    daten_formatiert = [];
                    datensatz = [];
                    for (y in spalten) {
                        for (z in werte) {

                            daten_formatiert.push(werte[z][spalten[y]]);
                        }
                        datensatz[y] = daten_formatiert;
                        daten_formatiert = [];
                    }

                    var labeltagunduhrzeit = [];
                    if($('#charttyoe').val()=="month"){
			for (var i in werte) {
                        date = new Date(werte[i].tag)

                        labeltagunduhrzeit.push(werte[i].monat +" " + werte[i].jahr);
                    	}
		}else{
		    	for (var i in werte) {
                        	date = new Date(werte[i].tag)

                        	labeltagunduhrzeit.push(date.toDateString());
                    	}

		    }

                    auswahl = $('#spaltenauswahl').val()
                    h = 0;
                    j = 0;

                    var chartdata = {
                        labels: labeltagunduhrzeit,
                        datasets: []
                    };

                    for (h in spalten) {

                        for (j in auswahl) {
                            farbe = 'rgba(' + Math.floor(Math.random() * 225) + ', ' + Math.floor(Math.random() * 225) + ', ' + Math.floor(Math.random() * 225) + ', 0.6)';
                            if (spalten[h] == auswahl[j]) {
                                chartdata['datasets'].push({
                                    label: spalten[h],
                                    backgroundColor: farbe,
                                    borderColor: farbe,
                                    hoverBackgroundColor: farbe,
                                    hoverBorderColor: farbe,
                                    fill: true,
                                    data: datensatz[h],

                                });


                            }
                        }



                    }

                    $('#chart-container').empty();
                    document.getElementById("chart-container").innerHTML = '<canvas id="mycanvas"></canvas>';

                    var ctx = $("#mycanvas");


                    barGraph = new Chart(ctx, {
                        type: $('#types').val(),
                        data: chartdata,
                        options: {
                            //responsive: true,
                            hoverMode: 'index',
                            stacked: true,
                            title: {
                                display: true,
                                text: 'HA Auswertung'
                            },

                        }
                    });

                    //$('#legende').html(barGraph.generateLegend()); 
                },
                error: function (data) {
                    console.log(data);
                }
            });
        });



    });
