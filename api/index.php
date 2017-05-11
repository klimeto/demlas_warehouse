<?php ?>
<html>
	<head>
	<link rel="shortcut icon" href="https://demlas.geof.unizg.hr/theme/image.php/aardvark/theme/1493373374/favicon">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<style>
		#myProgress {
		  width: 100%;
		  background-color: #ddd;
		}

		#myBar {
		  width: 10%;
		  height: 30px;
		  background-color: #4CAF50;
		  text-align: center;
		  line-height: 30px;
		  color: white;
		}
	</style>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script>
		$( document ).ready(function() {
			
			function move() {
			  var elem = document.getElementById("myBar");   
			  var width = 10;
			  var id = setInterval(frame, 60);
			  function frame() {
				if (width >= 100) {
				  clearInterval(id);
				} else {
				  width++; 
				  elem.style.width = width + '%'; 
				  elem.innerHTML = width * 1  + '%';
				}
			  }
			}
			function createURL(){
				var rootDir = $('#rootDir').val();
				var rootWS = $('#rootWS option:selected').val();
				var executionURL = "directory2geosworldimagestores.php?";
				if (rootDir != ''){
					executionURL += 'rootDir=' + rootDir +'&';	
				}
				if (rootWS != ''){
					executionURL += 'rootWS=' + rootWS +'&';	
				}
				executionURL = executionURL.slice(0,-1);
				$('#executionURL').attr('href',executionURL);
				$('#executionURL').text(executionURL);
				
			}
			
			$("#executeHarvesting").click(function runHarvesting(){
				$("#myProgress").hide();
				$("#myProgress").show();
				move();
				var category = $('#executeHarvesting').text();
				var harvTaskId;
				if (category === '- Select Category -' || category === '- No category -'){
					return alert('* Select Metadata Category');
				}
				if (category === 'demlas_oi'){
					harvTaskId = 676;
				}
				
				if (category === 'demlas_lc'){
					harvTaskId = 819;
				}
				
				if (category === 'demlas_cp'){
					harvTaskId = 724;
				}
				
				if (category === 'demlas_el_cov'){
					harvTaskId = 8729;
				}
				if (category === 'demlas_el_tin'){
					harvTaskId = 9035;
				}
				if (category === 'demlas_el_vec'){
					harvTaskId = 9134;
				}
				if (category === 'demlas_elu'){
					harvTaskId = 9233;
				}
				$( "#progressbar" ).progressbar({
				  value: 20
				});
				if (category === 'demlas_om'){
					harvTaskId = 1025;
				}
				
				var loginURL = 'https://demlas.geof.unizg.hr/geonetwork/j_spring_security_check';
				var harvestingURL = 'https://demlas.geof.unizg.hr/geonetwork/srv/eng/admin.harvester.run?_content_type=json&id=' + harvTaskId;
				var logoutURL = 'https://demlas.geof.unizg.hr/geonetwork/j_spring_security_logout';
				$.post(loginURL, {username: 'harvester', password: 'Only4Harvesting', redirectUrl: ''},
						{headers: { 'Content-Type': 'application/x-www-form-urlencoded'}})
				.fail(function(error){
					consolo.log("###### ERROR IN GEONETWORK LOGIN" + error + "######");
					
				})
				.then(function(data){
					console.log("###### GEONETWORK LOGIN PASSED ######");
					return $.get(harvestingURL);	
				})
				.then(function(data){
					console.log("###### HARVESTING PASSED WITH DATA: ", data);
					return $.get(logoutURL);
				});
			})
			
			$('#rootDir').on('change', function(){
				createURL();
			});
			$('#rootWS').on('change', function(){
				createURL();
			});
			$('#category').on('change', function(){
				$('#executeHarvesting').text($('#category option:selected').val());
			})
			
			
		});
			
	
	</script>
	</head>
	<body>
	<h1>Demlas warehouse API page</h1>
	<div>
		<p>This page provides links to launch batch processes in the warehouse as follows:
			<ul>
				<li>
					<h3>Bulk metadata generation from information stored in Google Sheet and storage in Dublin Core XML serialization.</h3>
					<hr>
					<span>NOTE: Configuration required!</span>
					<hr>
					Execute: <a href="goosheet2dcxml.php"><pre>goosheet2dcxml.php<pre></a>
				</li>
				<li>
					<h3>Bulk metadata generation from information stored in Google Sheet and storage in ISO Geographic metadata XML serialization.</h3>
					<hr>
					<span>NOTE: Configuration required!</span>
					<hr>
					Execute: <a href="goosheet2isoxml.php"><pre>goosheet2isoxml.php<pre></a>
				</li>
				<li>
					<h3>Bulk data publication from TIF+TFW+PRJ files stored in warehouse folder into Geoserver as WorldImage data stores.</h3>
					<hr>
					<span>NOTE: Configuration required!</span><br><br>
					<span>ROOT DATA DIRECTORY: <input type="text" id="rootDir" placeholder="e.g. /opt/demlas/cp/georef/"></span><br><br>
					<span>DATA THEME: <select id="rootWS">
										<option value="default">- Select Theme -</option>
										<option value="demlas_lc">Land Cover</option>
										<option value="demlas_oi">Orthoimagery</option>
										<option value="demlas_cp">Cadastral Parcel</option>
									  </select>
					</span>
					<hr>
					
					Execute: <a id="executionURL" href="directory2geosworldimagestores.php" target="_new">directory2geosworldimagestores.php</a>
					
				</li>
				<li>
					<h3>Metadata harvesting from a local directory to warehouse catalogue.</h3>
					<hr>
					<span>NOTE: Configuration required!</span>
					<hr>
					<span>METADATA CATEGORY: <select id="category">
										<option value="default">- Select Category -</option>
										<option value="demlas_lc">Land Cover</option>
										<option value="demlas_oi">Orthoimagery</option>
										<option value="demlas_cp">Cadastral Parcel</option>
										<option value="demlas_el_cov">Elevation Coverage</option>
										<option value="demlas_el_vec">Elevation Vector</option>
										<option value="demlas_el_tin">Elevation TIN</option>
										<option value="demlas_elu">Existing Land Use</option>
										<option value="demlas_om">Observations and Measurements</option>
									  </select>
					</span>
					<hr>
					Execute harvesting tast: <button id="executeHarvesting">- No category -</button>
					<hr>
					<div id="myProgress" style="display:none;">
					  <div id="myBar">10%</div>
					</div>
					<hr>
				</li>
				
				
				
			</ul>
		
		
		
		
		</p>
	</div>
	
	
	
	</body>

</html>