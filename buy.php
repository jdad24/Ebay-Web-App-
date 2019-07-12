<?php
session_start();
//session_unset();
//session_destroy();
//$_SESSION['total'];
//$total = 0;
function reindex(&$x) {
$temp = $x;
$x = array();
foreach($temp as $value) {
$x[] = $value; 
} 
}
?>


<html>
<head>
	
	<style>
		body {background-color: skyblue;}
		table, th,td,tr {
			border: 1px solid black;
			width: 100%;
		}
	</style>
	
	<title>Buy Products</title>
	
</head>

<body>
	<form action="buy.php" method="GET"> 
		<input type="submit" value="Empty Cart"> 
		<input type="hidden" name="clear" value="1">
		<?php
		if(isset($_GET['clear']))
		{
			session_unset();
			session_destroy();
			//echo("Clear");
		}
		?>
	</form> </br>
	
	
	<form action="buy.php" method="GET">
		<fieldset>
			<legend>Search Products:</legend>
			
			<label>Category: 
				<?php
				//List Categories in Select Tag
$allcat =  file_get_contents("http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId=72&showAllDescendants=true");
				 $simpleallcat = new SimpleXMLElement($allcat);
				
				 echo("<select name='cat'><option value=".$simpleallcat->category['id'].">".$simpleallcat->category->name."</option>");
				 foreach($simpleallcat->category->categories->category as $cat1)
				 {
					 echo("<optgroup label=".$cat1->name.">");
					 foreach($cat1->categories->category as $item)
					 {
					echo("<option value=".$item['id'].">");
				 	echo($item->name."</option>");
				}
				echo("</optgroup>");
				 }
				 echo("</select>");
				 ?>
			</label>
			
			<label>Search Keywords: <input type="text" name="keyword"></label>
				<input type="submit" name="submit" value="Search">	
				
		</fieldset>
	</form> 
	
	<div>
		<fieldset>
		<legend>Search Results:</legend>
		
		<?php
		//Show Search Results
		if(isset($_GET["submit"])) {
			$selectedcat = $_GET['cat']; 
			$key = $_GET['keyword'];	
			$_SESSION['key'] = (string)$key;
			$_SESSION['selectedcat'] = (string)$selectedcat;
			
			
			$results = file_get_contents("http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&categoryId=".$selectedcat."&keyword=".$key."&numItems=20");
			
			$simpleresults = new SimpleXMLElement($results);
			$_SESSION['simpleresults'] = (string)$simpleresults;

			
			echo("<table>
				<tr>
			<th>Name</th>
			<th>Description</th>
			<th>BasePrice</th>
			</tr>");
			
			//Output Search Results
			foreach($simpleresults->categories->category->items->offer as $offer) 
			{
				echo("<tr>");
				//echo("<td>".$offer['id']."</td>");
				echo("<td><a href=buy.php?buy=".$offer['id'].">".$offer->name."</a></td>");
				echo("<td>".$offer->description."</td>");
				echo("<td>".$offer->basePrice."</td>");
				echo("</tr>");
				
				//$list[(string)$offer['id']] = $offer->name;
				
			}
			//echo($list['x2B9Bxhe_61hbvE57IgSOA==']);
			
		
			echo("</table>");
			
		}		
			
		?>
		</fieldset>
	</div>
	
	<div><h3>Shopping Cart</h3>
	
		<table>
			
		<?php
		//Add to Shopping Cart
		if(isset($_GET['buy'])) {
			$purchase = $_GET['buy'];
			$total = 0;
			
			if(!isset($_SESSION['name'])){
				$_SESSION['name'] = array();
			}

			if(!isset($_SESSION['price'])){
				$_SESSION['price'] = array();
			}
			if(!isset($_SESSION['id'])){
				$_SESSION['id'] = array();
			}
			if(!isset($_SESSION['total'])){
				$_SESSION['total'] = 0;
			}
			
			$results = file_get_contents("http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&categoryId=".$_SESSION['selectedcat']."&keyword=".$_SESSION['key']."&numItems=20");
			
			$simpleresults = new SimpleXMLElement($results);
			
			foreach($simpleresults->categories->category->items->offer as $offer)
			{
				if($offer['id']==$_GET['buy'])
				{
					array_push($_SESSION['id'], (string)$offer['id']);
					array_push($_SESSION['name'], (string)$offer->name);
					array_push($_SESSION['price'], (string)$offer->basePrice);
					$cprice = current($_SESSION['price']);
					$cid = current($_SESSION['id']);
					
					
					
					foreach($_SESSION['name']as $arrayname)
					{
						echo("<tr>
								<td>".$arrayname."</td>"."<td>".$cprice."</td><td><a href=buy.php?delete=".$cid.">Delete</a></td></tr>");
						$total = $total + floatval($cprice);
						$cprice = next($_SESSION['price']);
						$cid = next($_SESSION['id']);
						$_SESSION['total'] = (string)$total;
					}
					
					/*foreach($_SESSION['price'] as $arrayprice)
					{
						echo("<tr><td>".$arrayprice."</td></tr>");
					}*/
					
					
					
					break;
				}
			}	
			
					
		}
	
	
	//Delete Section	
		if(isset($_GET['delete']))
		{
			reset($_SESSION['id']);
			reset($_SESSION['price']);
			reset($_SESSION['name']);
			
			$cid = current($_SESSION['id']);
			//echo($cid);
			$counter=0;
			$total=0;
			
			foreach($_SESSION['id'] as $id)
			{
				$cprice = current($_SESSION['price']);
				$name = current($_SESSION['name']);
				
				if($id==$_GET['delete'])
				{
					//echo($id);
					//echo($cprice);
					unset($_SESSION['id'][$counter]);
					//$_SESSION['id'] = array_values($_SESSION['id']);
					reindex($_SESSION['id']);
					unset($_SESSION['price'][$counter]);
					//$_SESSION['price'] = array_values($_SESSION['price']);
					reindex($_SESSION['price']);
					unset($_SESSION['name'][$counter]);
					//$_SESSION['name'] = array_values($_SESSION['name']);
					reindex($_SESSION['name']);
					echo("<br>Deleted: ".$name);
					break;
				}
				$cprice = next($_SESSION['price']);
				$cname = next($_SESSION['name']);
				$counter = $counter+1;
			}
			
			//Reset Session Arrays in order to navigate
			reset($_SESSION['id']);
			reset($_SESSION['price']);
			reset($_SESSION['name']);
			$cprice = current($_SESSION['price']);
			$name = current($_SESSION['name']);
			
			echo("<table>");
			
			foreach($_SESSION['id'] as $oid)
			{
				echo("<tr><td>".$name."</td><td>".$cprice."</td><td><a href=buy.php?delete=".$oid.">Delete</a></td></tr>");
				$total += floatval($cprice);
				$cprice = next($_SESSION['price']);
				$name = next($_SESSION['name']);
				$_SESSION['total'] = (string)$total;
			}
			
			echo("</table>");
		
			}
			
			//Outside Buy/Delete if statements
			if(!isset($_SESSION['total']) or (count($_SESSION['id'])==0))
			{
			echo("Total: $0");
		
		} else {
			echo("Total: $".$_SESSION['total']);
		}
		
		//print_r($_SESSION['price']);
		?>
		
	</table>
	</div>
	

</body>
</html>
