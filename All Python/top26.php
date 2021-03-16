<?php
if(!function_exists('xlLoadWebSmartObject')) {
		function xlLoadWebSmartObject($file, $class) {	if($file !== $_SERVER["SCRIPT_FILENAME"]) {	return;	} $instance = new $class; $instance->runMain(); }
}
//	Program Name:		TopSoldSKU.php
//	Program Title:		top 100 skus
//	Created by:			gsrungarakavi
//	Template family:	Responsive
///	Template name:		Page at a Time (DataTables).tpl
//	Purpose:			Record modification using a jQuery Grid
//	Program Modifications:

require_once('/esdi/websmart/v11.2/include/WebSmartObject.php');
require_once('/esdi/websmart/v11.2/include/xl_functions.php');

/*--------Include Dyanamic labriry----------*/
require_once('../PDOConnectionConfig.php');
global $mm4r6l,$mm4r6c,$funclab,$loadglobal,$pgurl;
$conpdo 		= new PDO_config();
$conOptionspdo 	= $conpdo->PDO_options;
$mm4r6l			= $conOptionspdo['datalib']; //mm4r6dvl,mm4r6itl,mm4r6lib
$mm4r6c 		= $conOptionspdo['controllib']; //mm4r6dvc,mm4r6itc,mm4r6ctl
$funclab 		= $conOptionspdo['funclab'];
$loadglobal		= $conOptionspdo['loadglobal'];
$pgurl			= $conOptionspdo['pgurl'];
//$mm4r6l		= 'mm4r6itl';$mm4r6c 	= 'mm4r6itc';
///*---------------ENd------------------------*/

class TopSoldSKU extends WebSmartObject
{
	const RETAIL = "R";
	const QTYSOLD = "Q";
	const RETAILINDEX = 6;
	const QTYSOLDINDEX = 7;
	const DFLTTOPSOLD = 100;
	protected $loadglobal = "";
	protected $pgurl = "";
	protected $tableId = "TopSoldSKU";    // Used in JavaScript and HTML to identify grid components: grid, Create, Update and Delete dialogs.
	protected $objectName = "Record";  // Name of business object. Eg: Client, Item, Vendor, Time Entry, etc.
	protected $programState = array(
		'listSize' => 100,
		'sortDir' => '', 
		'sort' => 'desc',
		'sortIndex' => self::QTYSOLDINDEX,
		'filters' => array('ITRLOC' 	=> '','ITRDAT' => '','IDEPT'=> '','TOP'=> '','TYPE'=> '','today'=> 0,'ARGSTYL'=> '0'),'today' => 0
	);
	
	public function runMain()
	{
		// Connect to the database
		try 
		{
			$this->db_connection = new PDO(
			'ibm:' . $this->defaults['pf_db2SystemName'], 
			$this->defaults['pf_db2UserId'], 
			$this->defaults['pf_db2Password']
			);			
		
			global $mm4r6l,$mm4r6c,$funclab,$loadglobal,$pgurl;		
			$this->loadglobal = $loadglobal;
			$this->pgurl = $pgurl;
		}
		catch (PDOException $ex)
		{
			die('Could not connect to database: ' . $ex->getMessage());
		}
		
		// Fetch the program state
		$this->getState();
		
		$this->programState['today'] = date("ymd");

		
		// Run the specified task
		switch($this->pf_task)
		{
			// Construct the main grid page
			case 'default':
			$this->prepareGrid();
			break;
			
			// Output the grid contents
			case 'loadgrid':
			$this->loadGrid();
			break;
			
			// Set filters and output the filtered grid contents
			case 'filter':
			$this->filterRecords();
			break;
		}				
	}
	
	// Write the page framework (supporting javascript, css, HTML elements) to construct the grid shell 
	// and invoke an AJAX call to load the first page of data. 	
	protected function prepareGrid()
	{
		
		$this->programState['sortIndex'] = self::QTYSOLDINDEX;
		$this->programState['sort'] = 'desc';
		
		// rizwan 02/12/18
		if(!isset($this->programState['filters']['TOP']) || $this->programState['filters']['TOP'] == '')
		{
			$this->programState['filters']['TOP'] = self::DFLTTOPSOLD;
		}
		// This will always be defined on the URL, if it isn't it redirects to another program to set it
		$this->programState['filters']['ITRLOC'] = base64_decode(xl_get_parameter('Store'));

		$this->updateState();
		$this->writeSegment('GridContainer', array_merge(get_object_vars($this), get_defined_vars())); 
	}
	
	// Load the data for the grid. It gets returned to the client as a JSON object.
	// Also check for changes in the sort column and direction
	protected function loadGrid()
	{
		$this->programState['filters']['ARGSTYL']=0;//Make checkbox value 0 because on page load it take previous value
		// Update the program with any changes to how we are viewing the data
		$this->updateState();
		
		// Build first page of table rows
		$this->buildPage();
	}
	
	// Output a set of up to listSize # of records for the grid
	protected function buildPage()
	{
		header('content-type: text/plain');
		
		$start = (int) xl_get_parameter('start') + 1;
		$draw = (int) xl_get_parameter('draw');
		$listSize = (int) $this->programState['filters']['TOP'];
		
		$totalRows = $this->getListRowCount();
		
		// Setup JSON array to hold records
		$jsonRecordPage = array();
		$jsonRecordPage['draw'] = $draw;
		$jsonRecordPage['recordsTotal'] = $totalRows;
		$jsonRecordPage['recordsFiltered'] = $totalRows;
		$jsonRecordPage['data'] = array();

		// Create and execute the list Select statement
		$stmt = $this->buildListStmt();
		if (!$stmt)
		{
			$this->dieWithPDOError($stmt);
		}
		
		// Fetch the first row for page
		$result = $stmt->execute();
		if (!$result)
		{
			$this->dieWithPDOError($stmt);
		}
		
		$row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_REL, $start);
	
		// Build a list of up to listSize # of records
		$rowCount = 0;			
		while($row and ($rowCount < $listSize))
		{
			// Do any operations (such as formatting) on a single row's data here.			
			// Add the row data to the output		
			$jsonRecordPage['data'][] = $this->getRowData($row);
			// Fetch next row			
			$row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
			$rowCount+=1;
		}
		
		$jsonRecordPage['recordsTotal']=$rowCount;
		$jsonRecordPage['recordsFiltered']=$rowCount;
				
		// Output the JSON for this page
		echo json_encode($jsonRecordPage);
	}
	
	// Build the List statement
	protected function buildListStmt()
	{
		global $mm4r6l,$mm4r6c,$funclab,$loadglobal,$pgurl;
		// Build the query with parameters
		
		$DetailQueryString  =  'WITH c1 AS ( ';			
			$DetailQueryString .= ' ' . $this->buildSelectClauseForAUD();
			$DetailQueryString .= ' ' . $this->buildWhereClauseForAUD();
			$this->programState['filters']['ARGSTYL'];
			if($this->programState['filters']['ARGSTYL'] != 0)
			{ 	  $DetailQueryString .= ' ' . $this->buildGroupbyClauseForDEPT();	}
			else{ $DetailQueryString .= ' ' . $this->buildGroupbyClauseForAUD(); }
			
			if($this->programState['filters']['TYPE'] === self::RETAIL)
			{	$DetailQueryString .= " ORDER BY  RETAIL DESC"; 	}
			else
			{	$DetailQueryString .= " ORDER BY QTYSOLD DESC ";	}
		$DetailQueryString .= " )";
		
		if($this->programState['filters']['TYPE'] === self::RETAIL)
		{ $DetailQueryString .= "SELECT  RANK() OVER ( ORDER BY RETAIL DESC, c1.INUMBR ) AS RANK,"; }
		else
		{ $DetailQueryString .= "SELECT  RANK() OVER ( ORDER BY c1.QTYSOLD DESC, c1.INUMBR ) AS RANK, "; }
		
     	$DetailQueryString .= " c1.ITRLOC,c1.inumbr AS INUMBR, c1.idescr AS SKUDESCRIPTION,  
					       		DIGITS(c1.idept)||'-'||DIGITS(c1.isdept)||'-'||DIGITS(c1.iclas)||'-'||DIGITS(c1.isclas) AS DEPARTMENT,
					       		COALESCE(d.dptnam, '** NOT FOUND **') AS DESCRIPTION, "
					       		.$mm4r6l.".GETPRCFN(c1.ITRLOC, c1.INUMBR, 0, CAST(' ' AS CHAR(1)), CAST('".$funclab."' AS CHAR(3))) AS PRICE, 
					       		c1.retail AS RETAIL, c1.qtysold AS QTYSOLD, COALESCE(b.ibhand, 0) AS ONHAND,b.IBPOOQ AS POONORDER,
					       		c1.CLRANCESKU, c1.ISTYLN, c1.ISCOLR, c1.ISSIZE
					       		FROM c1  
					       		LEFT OUTER JOIN ".$mm4r6l.".invdpt D ON D.idept = c1.idept AND 
					       		D.isdept = c1.isdept AND D.iclas = c1.iclas AND D.isclas = c1.isclas 
					       		LEFT OUTER JOIN ".$mm4r6l.".invbal B ON B.istore = c1.itrloc AND B.inumbr = c1.inumbr  ";       
       $DetailQueryString .= ' ' . $this->buildOrderBy(). ' FETCH FIRST '. $this->programState['filters']['TOP'].' ROWS ONLY ';
       
		//echo $DetailQueryString;
		// Prepare the statement
		try{
			$stmt = $this->db_connection->prepare($DetailQueryString, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));			
		}		
		catch (PDOException $ex)
		{	die('Could not connect to database: ' . $ex->getMessage());		}
		if (!$stmt)
		{ return $stmt; }
		return $stmt;
	}
	
	// Build SQL Select string for the main list
	protected function buildSelectClauseForAUD()
	{
		global $mm4r6l,$mm4r6c,$funclab,$loadglobal,$pgurl;
		// Note: AED - make 222222, 333333 constants to describe what they are 
		 $selString = "SELECT A.ITRLOC, A.INUMBR, I.IDESCR, A.IDEPT, A.ISDEPT, A.ICLAS, A.ISCLAS, SUM(A.ITRQTY) AS QTYSOLD,SUM(ITTRET) AS RETAIL, 
					COALESCE(	(SELECT 'Y' FROM ".$mm4r6l.".IDECLR CLR 
								WHERE CLR.CLRITM = A.INUMBR AND CLR.CLRLOC = A.ITRLOC
								FETCH FIRST 1 ROWS ONLY), 
								(SELECT 'Y' FROM ".$mm4r6l.".PRCPLN                                    
								WHERE plnstr = A.ITRLOC AND plnitm = A.INUMBR  
								AND plncdt <= ".trim($this->programState['today'])." 
								AND plnedt >= ".trim($this->programState['today'])."     
								AND plnevt > 999994
								FETCH FIRST 1 ROWS ONLY),'N') AS CLRANCESKU,
					COALESCE(I.ISTYLN,' ') AS ISTYLN,COALESCE(I.ISCOLR,' ') AS ISCOLR,COALESCE(I.ISSIZE,' ') AS ISSIZE
					FROM ".$mm4r6l.".INVAUD AS A 
					JOIN ".$mm4r6l.".INVMST AS I ON I.INUMBR = A.INUMBR AND I.ASNUM NOT IN ( 222222, 333333 ) 
					JOIN ".$mm4r6c.".TBLFLD AS T ON digits(A.ITRTYP) = TRIM(T.TBLVAL) AND T.TBLNAM = 'TSTRTY' "; 
	   return $selString;
	}

	// Note: AED - add function header comments
	protected function buildGroupbyClauseForAUD()
	{
		return	' GROUP BY A.ITRLOC, A.INUMBR, I.IDESCR, A.IDEPT, A.ISDEPT, A.ICLAS, A.ISCLAS,I.ISTYLN,I.ISCOLR,I.ISSIZE';		
	}

	// Note: AED - add function header comments	
 	protected function buildGroupbyClauseForDEPT()
	{
		return	'GROUP BY I.ISTYLN,A.ITRLOC, A.INUMBR, I.IDESCR, A.IDEPT, A.ISDEPT, A.ICLAS, A.ISCLAS,I.ISCOLR,I.ISSIZE';
	}	
	// Build where clause to filter rows from table
	protected function buildWhereClauseForAUD()
	{
		global $mm4r6l,$mm4r6c,$funclab,$loadglobal,$pgurl;
		
		$whereClause = '';
		$link = ' WHERE ';
		
		//Store & department condition complsory
		if (!empty($this->programState['filters']['ITRLOC'])) 
		{
			$whereClause = $whereClause . $link . " A.ITRLOC =".$this->programState['filters']['ITRLOC'];				
			$link = ' AND ';
		}
	
		// Filter by ITRDAT - Transaction date
		if (!empty($this->programState['filters']['ITRDAT'] ))
		{
			$seldtrng = @explode('-',trim($this->programState['filters']['ITRDAT']));	
			$FROM= $seldtrng[0];
			$End = $seldtrng[1];
					
			$FromDate = date('ymd', strtotime($FROM));			
			$EndDate = date('ymd', strtotime($End));			
			
			if ($FromDate != '' && $EndDate != '') 
			{
				$whereClause = $whereClause . $link . ' A.ITRDAT BETWEEN '. $FromDate .' AND '. $EndDate ;
				$link = " AND";
			}
			
			if ($FromDate != '' && $EndDate == '') 
			{
				$whereClause = $whereClause . $link . ' A.ITRDAT >= '. $FromDate;
				$link = " AND";
			}
			if ($FromDate =='' && $EndDate != '') 
			{
				$whereClause = $whereClause . $link . ' A.ITRDAT <= '. $EndDate;
				$link = " AND ";
			}
						
			$FROM = '';
			$End  = '';
		}
		else
		{
			$today = new DateTime() ;
			$weekbefore = new DateTime(); 
			$weekbefore->sub(new DateInterval("P7D"));
			$EndDate = $today->format("ymd");
			$FromDate = $weekbefore->format("ymd");
			$whereClause = $whereClause . $link . ' A.ITRDAT BETWEEN '. $FromDate . ' AND ' . $EndDate;;
			$link = " AND";
		}
		
	
		// Filter by IDEPT
		if (($this->programState['filters']['IDEPT'] != '') and ($this->programState['filters']['IDEPT'] != '0'))
		{		
			 	$dept = @explode(',',$this->programState['filters']['IDEPT']);//explode department in array
				if(count($dept) > 0){// if count is more than 0
					for($i=0;$i<count($dept);$i++){//
						if(!empty($dept[$i]) && $dept[$i]>=1){
							$strtdpt = ($dept[$i]*100);//multiply with 100 eg.3*100 = 300
							$enddept = $strtdpt+99; //add 99 in start dept eg.300+99 = 399
							
							if($i==0){
								$cndlp = " (A.IDEPT BETWEEN ".$strtdpt." AND ".$enddept;	
							}
							else{
								$cndlp .= " OR A.IDEPT BETWEEN ".$strtdpt." AND ".$enddept."";								
							}
							
							if(($i+1)==count($dept)){$cndlp .= " )";}
						}
					}
				} 
		 	$whereClause = $whereClause . $link . $cndlp;
		 	$link = ' AND ';			
		}
		
		//Clearance SKU
		if (!empty($this->programState['filters']['CLRANCESKU']) && ($this->programState['filters']['CLRANCESKU']== 'Y'))
		{
			// Note: AED - Make 999994 a constant to describe it's purpose
			$whereClause = $whereClause . $link . "
						COALESCE(
							(SELECT 'Y' FROM ".$mm4r6l.".IDECLR CLR 
							WHERE CLR.CLRITM = A.INUMBR AND CLR.CLRLOC = A.ITRLOC
							FETCH FIRST 1 ROWS ONLY), 
							(SELECT 'Y' FROM ".$mm4r6l.".PRCPLN                                    
							WHERE plnstr = A.ITRLOC AND plnitm = A.INUMBR  
							AND plncdt <= ".trim($this->programState['today'])." 
							AND plnedt >= ".trim($this->programState['today'])."     
							AND plnevt > 999994
							FETCH FIRST 1 ROWS ONLY),'N') != 'Y' ";
			$link = " AND ";
		}			
		return $whereClause;
	}
	
	// Build order by clause to order rows
	protected function buildOrderBy($c1='')
	{
			// Set sort order to programState's sort by and direction
			$orderBy = " ORDER BY " . $this->programState['sort'] . ' ' . $this->programState['sortDir'];
			return $orderBy;
	}
	
	// Return an array containing data from a row
	protected function getRowData($row,$rowno='')
	{
		// Array of columns used in the record table
		$listColumns = array('RANK','INUMBR', 'SKUDESCRIPTION', 'DEPARTMENT', 'DESCRIPTION','PRICE','RETAIL', 'QTYSOLD', 'ONHAND','POONORDER','CLRANCESKU','ISTYLN','ISCOLR','ISSIZE');
		
		// Setup an array to contain the row data
		$rowData = array();
	
		foreach($listColumns as $column) 
		{			
			$stylcd = trim($row['ISTYLN']);
			$deptval = substr($row['DEPARTMENT'], 0,3);
			$agrchk = $this->programState['filters']['ARGSTYL'];
			
			if($column=='INUMBR'){
				if($agrchk!=0 && ($deptval>499 || $deptval < 700) && !empty($stylcd)){
					$rowValue ='';
				}
				else{
					$rowValue = utf8_encode(trim($row[$column]));					
				}
				$rowData[] = htmlspecialchars($rowValue, ENT_QUOTES);
			}			
			elseif($column=='DEPARTMENT')
			{
				if($agrchk!=0 && ($deptval>499 || $deptval < 700) && !empty($stylcd)){
					$deptval='';
				}
				else{
					$rowValue = utf8_encode(trim($row[$column]));
					$depexp = @explode('-',$rowValue);
					$deptarr = array();
					for($d=0;$d<count($depexp);$d++)
					{
						if(!empty($depexp[$d]) && $depexp[$d]>0){
							$deptarr[]= str_pad($depexp[$d],3,"0",STR_PAD_LEFT);
						}
					}
					$deptval = @implode('-',$deptarr);						
				}								
				$rowData[] = htmlspecialchars($deptval, ENT_QUOTES);
			}
			else if($column=='DESCRIPTION'){
				if($agrchk!=0 && ($deptval>499 || $deptval < 700) && !empty($stylcd)){
					$deptnm='';
				}
				else{
					$deptnm = utf8_encode(trim($row[$column]));
				}
				$rowData[] = htmlspecialchars($deptnm, ENT_QUOTES);
			}
			else if($column=='RETAIL' || $column =='PRICE')
			{
				$rowValue = utf8_encode(trim($row[$column]));				
									
				$rowData[] = '$ '.number_format($rowValue, 2, '.', ',');
			
			}
			else if($column=='POONORDER')
			{
				if($agrchk!=0 && ($deptval>499 || $deptval < 700) && !empty($stylcd)){
						$rowData[] = '';
				}
				else{					
					$rowValue = round(utf8_encode(trim($row[$column])));				
					if(!empty($rowValue) || $rowValue!='' || $rowValue!=0 || $rowValue!='.00')					{
						if(round($rowValue) < 0){
							$disppo = '<span style="color:red">'.round($rowValue).'</span>';
						}
						else{								
							$disppo = number_format(round($rowValue),0, '.', ',');
						}
						$rowData[] = $disppo;
					}
					else{
						$rowData[] = '';	
					}
				}				
			
			}						
			else if($column == 'QTYSOLD' || $column == 'ONHAND'){
				if($agrchk!=0 && ($deptval>499 || $deptval < 700) && !empty($stylcd)){
						$disp = '';
				}
				else{
					if($agrchk!=0 && ($deptval>499 || $deptval < 700) && !empty($stylcd)){
						$disp='';
					}
					else{
						$rowValue = utf8_encode(trim($row[$column]));
						if(round($rowValue) < 0){
							$disp = '<span style="color:red">'.round($rowValue).'</span>';
						}
						else{
								
							$disp = number_format(round($rowValue),0, '.', ',');
						}
					}
				}
				$rowData[] = $disp;
			}
			else if($column == 'ISCOLR'){
				if($agrchk!=0 && ($deptval>499 || $deptval < 700) && !empty($stylcd)){
					$rowValuefn = '';
				}
				else{
					$rowValue = utf8_encode(trim($row[$column]));
					if(!empty($rowValue)){
						$colrnm = $this->GetColorName($rowValue);
						if(!empty($colrnm)){$rowValuefn = $colrnm; } else{ $rowValuefn = '-';} 
					}
					else{
						$rowValuefn = '-';
					}
				}
				$rowData[] = htmlspecialchars($rowValuefn,ENT_QUOTES);
			}
			else if($column == 'ISSIZE'){
				if($agrchk!=0 && ($deptval>499 || $deptval < 700) && !empty($stylcd)){
					$rowValuefn = '';
				}
				else{
					$rowValue = utf8_encode(trim($row[$column]));
					if(!empty($rowValue)){
						$siznm = $this->GetSizeName($rowValue);
						if(!empty($siznm)){$rowValuefn = $siznm; } else{ $rowValuefn = '-';} 
					}
					else{
						$rowValuefn = '-';
					}
				}
				$rowData[] = htmlspecialchars($rowValuefn,ENT_QUOTES);
			}
			else if($column == 'ISTYLN'){
				if($agrchk!=0 && ($deptval>499 || $deptval < 700) && !empty($stylcd)){
					$rowValue = utf8_encode(trim($row[$column]));
					if(!empty($rowValue)){
						//If Style not blank then appeare link
						$deptval = substr($row['DEPARTMENT'], 0,3);
						if($deptval > 499 || $deptval <= 700)
						{ 
							$stor = utf8_encode(trim($row['ITRLOC'])); 
							$skuno = utf8_encode(trim($row['INUMBR']));
							$styl = base64_encode(utf8_encode(trim($row['ISTYLN'])));
							$transdt = base64_encode(utf8_encode(trim($this->programState['filters']['ITRDAT'])));
					
						$rowValue = '<a href="javascript:aggrSKUDisp(\''.$stor.'\',\''.$skuno.'\',\''.$deptval.'\',\''.$styl.'\',\''.$transdt.'\')">'.$rowValue.'</a>'; 
						}
											
						$rowValuefn =$rowValue;
					}
					else{
						$rowValuefn = '-';
					}				
				}
				else{
					$rowValuefn = utf8_encode(trim($row[$column]));	
				}
				
				$rowData[] = $rowValuefn;
			}
			else if($column == 'SKUDESCRIPTION'){				
				// Note: AED - make dept values a constant to describe where 499 & 700 come from
				if($agrchk!=0 && ($deptval>499 || $deptval < 700) && !empty($stylcd)){
					$rowValue = "Aggregate Style";	
				}
				else{
					$rowValue = utf8_encode(trim($row[$column]));	
				}
				$rowData[] = htmlspecialchars($rowValue, ENT_QUOTES);

			}
			else{
				// Use htmlspecialchars to prevent cross site scripting attacks			
				$rowValue = utf8_encode(trim($row[$column]));
				$rowData[] = htmlspecialchars($rowValue, ENT_QUOTES);
			}		
		}		
		return $rowData;
	}
	
	// Return the total number of records based on the current filter values
	protected function getListRowCount()
	{
		// Build select query
		$selString = $this->getListCountSelect();
	  
		// Prepare the statement
		$stmt = $this->db_connection->prepare($selString);
		if (!$stmt)
		{
			return $stmt;
		}
		
		$result = $stmt->execute();
	
		if (!$result)
		{
			$this->dieWithPDOError($stmt);
		}
		$row = $stmt->fetch(PDO::FETCH_NUM);	
		return $row[0];
	}
	
	// Return the SQL SELECT for a count on the full list
	
 	protected function getListCountSelect()
	{
		global $mm4r6l,$mm4r6c,$funclab,$loadglobal,$pgurl;
		
		$DetailQueryStringForCount  =  'WITH c1 AS (';
		$DetailQueryStringForCount .= ' ' . $this->buildSelectClauseForAUD();
		$DetailQueryStringForCount .= ' ' . $this->buildWhereClauseForAUD();
		$DetailQueryStringForCount .= ' ' . $this->buildGroupbyClauseForAUD();	
		if($this->programState['filters']['TYPE'] === self::RETAIL)
		{	$DetailQueryStringForCount .= " ORDER BY  RETAIL DESC";		}
		else
		{	$DetailQueryStringForCount .= " ORDER BY QTYSOLD DESC ";	}
		
		$DetailQueryStringForCount .= " )";
		
		if($this->programState['filters']['TYPE'] === self::RETAIL)
		{	$DetailQueryStringForCount .= "SELECT  RANK() OVER ( ORDER BY RETAIL DESC, c1.INUMBR ) AS RANK,";	}
		else
		{	$DetailQueryStringForCount .= "SELECT  RANK() OVER ( ORDER BY c1.QTYSOLD DESC, c1.INUMBR ) AS RANK, ";	}

 	   $DetailQueryStringForCount .= "c1.inumbr AS INUMBR, c1.idescr AS SKUDESCRIPTION,  
									DIGITS(c1.idept)||'-'||DIGITS(c1.isdept)||'-'||DIGITS(c1.iclas)||'-'||DIGITS(c1.isclas) AS DEPARTMENT,
									d.dptnam AS DESCRIPTION,  c1.retail AS RETAIL, c1.qtysold AS QTYSOLD, b.ibhand AS ONHAND,b.IBPOOQ AS POONORDER
									FROM c1  
									JOIN ".$mm4r6l.".invdpt D ON D.idept = c1.idept AND D.isdept = c1.isdept AND D.iclas = c1.iclas AND D.isclas = c1.isclas 
									JOIN ".$mm4r6l.".invbal B ON B.istore = c1.itrloc AND B.inumbr = c1.inumbr  ";
		$DetailQueryStringForCount .= ' ' . $this->buildOrderBy();
		$DetailQueryStringForCount .= " FETCH FIRST ". $this->programState['filters']['TOP']." ROWS ONLY ";	

		return $DetailQueryStringForCount;	
	}	
	
	// Return the JSON string for the add/delete/change/display window
	protected function getRecordJson($row)
	{
		// Columns and keys used in record display
		// Stored as $rowColumn => $jsonColumn due to escaping of the JSON output
		$columns = array('ITRLOC_' => 'ITRLOC', 'ITRTYP_' => 'ITRTYP', 'ITRCEN_' => 'ITRCEN', 'ITRDAT_' => 'ITRDAT' , 'ITRLOC' => 'ITRLOC', 'ITRTYP' => 'ITRTYP', 'ITRCEN' => 'ITRCEN', 'ITRDAT' => 'ITRDAT' );
		
		// Fetch the normal column data
		$jsonRecord = array();
		foreach($columns as $jsonColumn => $rowColumn)
		{
			$jsonRecord[$jsonColumn] = '';
			if (isset($row[$rowColumn]))
			{
				$jsonRecord[$jsonColumn] = utf8_encode(trim($row[$rowColumn]));
			}
		}		
		// Encode the results as JSON and return them
		return json_encode($jsonRecord);
	}
	
	// Retrieve filter fields and output the record list JSON
	protected function filterRecords()
	{
		// Retrieve the filter information
		/*-------Explode value------*/
		$explodvl = @explode('-',trim(xl_get_parameter('ww_fIDEPT')));				
		
		$this->programState['filters']['ITRLOC'] 		= 	$explodvl[0];
		$this->programState['filters']['ITRDAT']  		=	$explodvl[1].'-'.$explodvl[2];
		$this->programState['filters']['IDEPT']  		=	$explodvl[3];
		$this->programState['filters']['TYPE']  		=	$explodvl[4];
		$this->programState['filters']['TOP']  			= (isset($explodvl[5]))?$explodvl[5]:self::DFLTTOPSOLD;//If TOP value balnak then default 100 		
		$this->programState['filters']['CLRANCESKU']  	= $explodvl[6];	//Clearance SKU
		$this->programState['filters']['ARGSTYL']  		= $explodvl[7];	//Aggregate Style
		
		if( $this->programState['filters']['TYPE'] === self::RETAIL)
		{
			$this->programState['sort'] = " desc ";
		 	$this->programState['sortIndex'] = self::RETAILINDEX;
		}
		else
		{
			$this->programState['sort'] = " desc ";
		 	$this->programState['sortIndex'] = self::QTYSOLDINDEX;
		}
		// Update the program with any changes to how we are viewing the data
		$this->updateState();		
		// Write out the record JSON
		$this->buildPage();
	}
	
	// Return a JSON message response string
	protected function jsonMessage($messageText, $messageType)
	{
		$jsonMessage['msg'] = $messageText;
		$jsonMessage['type'] = $messageType;
		return json_encode($jsonMessage);
	}
	
	// Update the program state - How and what information we are displaying
	protected function updateState()
	{
		// If a column header was clicked, sort parameters will be provided.
		// Update the program state to sort that way from now on
		$order = xl_get_parameter('order', 'db2_search');
		$columns = xl_get_parameter('columns', 'db2_search');
		if (isset($order[0]['column']))
		{
			$sortIndex = (int) $order[0]['column'];
			$sortOrder = $order[0]['dir'];
			$sortColumn = $columns[$sortIndex]['name'];
			// If no sort column is specified, use the unique keylist as the default
		 	if ($sortColumn == '' || $sortColumn == 'actions')
			{
				$this->programState['sort'] = " desc ";
			 	$this->programState['sortIndex'] = self::QTYSOLDINDEX;
			}
			else
			{
				if($this->pf_task=='filter' && $this->programState['filters']['TYPE'] === self::RETAIL)
				{ 
					$this->programState['sort'] = 'RETAIL';
				}
				else if($this->pf_task=='filter' && $this->programState['filters']['TYPE'] === self::QTYSOLD)
				{
					$this->programState['sort'] = 'QTYSOLD';
				}
				else{
					if($sortColumn == 'IDEPT'){
						$sortColumn = 'DEPARTMENT';
					}
					$this->programState['sort'] = $sortColumn;
					$this->programState['sortIndex'] = $sortIndex;
				}
			}
			if($this->pf_task=='filter' && ($this->programState['filters']['TYPE'] === self::RETAIL || 
						$this->programState['filters']['TYPE'] === self::QTYSOLD ))
			{
				$this->programState['sortDir'] = 'desc ' ;
			}
			elseif ($sortOrder == 'asc')
			{
				$this->programState['sortDir'] = 'asc ';
			}
			else 
			{
				$this->programState['sortDir'] = 'desc ';
			}	
		}
		
		// Update stored list size
		$listSize = xl_get_parameter('length', 'db2_search');
		if (!empty($listSize))
		{
			$this->programState['listSize'] = xl_get_parameter('length', 'db2_search');
		}
		
		$this->programState['today'] = date("ymd");
		
		// Save the state as a session variable
		$_SESSION[$this->pf_scriptname] = $this->programState;
	}
	// Note: AED - add function header comments
	protected function GetColorName($colcod)
	{
		global $mm4r6l,$mm4r6c,$funclab,$loadglobal,$pgurl;
		$rowReslt='';		
		$selString = "SELECT COLDSC FROM ".$mm4r6l .".TBLCOL WHERE COLCOD=:colcod";
		$stmt = $this->db_connection->prepare($selString, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->bindParam(':colcod', $colcod, PDO::PARAM_INT);
			if (!$stmt)
			{ $this->dieWithPDOError($stmt); }
			
			// Fetch the first row for page
			$result = $stmt->execute();
			if (!$result)
			{
				$this->dieWithPDOError($stmt);
			}
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				$rowReslt = $row['COLDSC'];	
			}
						
		return $rowReslt;
	}
	
	// Note: AED - add function header comments
	protected function GetSizeName($sizcod)
	{
			global $mm4r6l,$mm4r6c,$funclab,$loadglobal,$pgurl;
			$rowReslt='';			
				$selString = "SELECT SIZSHT FROM ".$mm4r6l .".TBLSIZ WHERE SIZCOD=:sizcod";
				$stmt = $this->db_connection->prepare($selString, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt->bindParam(':sizcod', $sizcod, PDO::PARAM_INT);
				if (!$stmt)
				{ $this->dieWithPDOError($stmt); }
				
				// Fetch the first row for page
				$result = $stmt->execute();
				if (!$result)
				{
					$this->dieWithPDOError($stmt);
				}
				while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
					$rowReslt = $row['SIZSHT'];	
				}
								
		return $rowReslt;	
	}	
	// Output the last PDO error, and exit
	protected function dieWithPDOError($stmt = false)
	{
		if ($stmt)
		{
			$err = $stmt->errorInfo();
		}
		else
		{
			$err = $this->db_connection->errorInfo();
		}
		// Note: AED - change this to a friendly error rather than a die statement
		die($this->jsonMessage('SQL Error #' . $err[1] . ' - ' . $err[2], 'error'));
	}
 

	function writeSegment($xlSegmentToWrite, $segmentVars=array())
	{
		foreach($segmentVars as $arraykey=>$arrayvalue)
		{
			${$arraykey} = $arrayvalue;
		}
		// Make sure it's case insensitive
		$xlSegmentToWrite = strtolower($xlSegmentToWrite);

	// Output the requested segment:

	if($xlSegmentToWrite == "gridcontainer")
	{

		echo <<<SEGDTA
<!DOCTYPE html>
<html>
  <head>
    <meta name="generator" content="WebSmart" />
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Pragma" content="no-cache" />
    <title>Top Sold SKUs</title>
    
     <link rel="stylesheet" href="/websmart/v11.2/css/bootstrap.min.css" type="text/css" />
    <link rel="stylesheet" href="/websmart/v11.2/css/ws_bootstrap.css" media="all" type="text/css" />
    <link rel="stylesheet" href="/WebSmart/v11.2/FollettNightFall/css/select.dataTables.min.css"/>
    <link rel="stylesheet" type="text/css" href="/WebSmart/v11.2/FollettNightFall/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.4.0/css/buttons.dataTables.min.css" />
    <link rel="stylesheet" type="text/css" href="/websmart/v11.2/Arctic/css/noheader.css" media="all" />
    <script type="text/javascript" src="/websmart/v11.2/js/jquery.min.js"></script>
    <script type="text/javascript" src="/websmart/v11.2/js/bootstrap.min.js"></script>  
    <script type="text/javascript" src="/websmart/v11.2/js/datatables.min.js"></script>
    <script type="text/javascript" src="/websmart/v11.2/js/ws_datatablesmanager.js"> </script>
    <script type="text/javascript" src="/WebSmart/v11.2/FollettNightFall/js/dataTables.select.min.js" > </script>    
    <!-- multi select script start -->
     <link rel="stylesheet" type="text/css" href="multiselect/bootstrap-chosen.css" media="all" />
     <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.7.0/chosen.jquery.js"></script>
     <!-- multi select script end -->      
    <script type="text/javascript" src="/websmart/v11.0/js/ws_datatablesmanager.js"></script>
    <link rel="stylesheet" type="text/css" href="/websmart/v11.0/Arctic/css/noheader.css" media="all" />
    <script type="text/javascript"> 
    </script>
    <!-- Load JS and CSS for Datepicker -->	
    <script type="text/javascript" src="/websmart/v11.2/js/jquery-ui.datepicker.min.js"></script>
	
	<script type="text/javescript" src="https://cdn.datatables.net/buttons/1.4.0/js/buttons.print.min.js"></script>
	
    <link rel="stylesheet" type="text/css" href="/websmart/v11.2/css/jquery-ui.datepicker.min.css"/>   
    <style>
    #ww_fIDEPTX_chosen{display:block !important}
    #wait{ position: absolute; top: 40%; left: 39%;width:20%;height:20%;z-index: 10;text-align:center;vertical-align:middle;}
	#wait img{width:120px;height:100px;}
    
    .table thead th:nth-child(1){min-width:20px !important} /*Rank*/
	.table tbody td:nth-child(1){min-width:20px !important} /*Rank*/
	
	.table thead th:nth-child(2){min-width:40px !important} /*SKU*/
	.table tbody td:nth-child(2){min-width:40px !important} /*SKU*/
	
	.table thead th:nth-child(3){min-width:280px !important} /*SKU Description*/
	.table tbody td:nth-child(3){min-width:280px !important} /*SKU Description*/
	
	.table thead th:nth-child(4){min-width:100px !important}/*Department*/
	.table tbody td:nth-child(4){min-width:100px !important}/*Department*/
	
	.table thead th:nth-child(5){min-width:280px !important}/*Department Name*/
	.table tbody td:nth-child(5){min-width:280px !important}/*Department Name*/
	
	.table thead th:nth-child(6){min-width:60px !important}/*Current Price*/
	.table tbody td:nth-child(6){min-width:60px !important}/*Current Price*/	
	
	.table thead th:nth-child(7){min-width:60px !important}/*Retail*/
	.table tbody td:nth-child(7){min-width:60px !important}/*Retail*/
	
	.table thead th:nth-child(8){min-width:80px !important}/*Qty Sold/Rented*/
	.table tbody td:nth-child(8){min-width:80px !important}/*Qty Sold/Rented*/
	
	.table thead th:nth-child(9){min-width:60px !important}/*MMS ON HAND*/
	.table tbody td:nth-child(9){min-width:60px !important}/*MMS ON HAND*/
	
	.table thead th:nth-child(10){min-width:60px !important}/*Po On order*/
	.table tbody td:nth-child(10){min-width:60px !important}/*Po On Order*/
	
	
	.table thead th:nth-child(11){min-width:80px !important}/*Clearance*/
	.table tbody td:nth-child(11){min-width:80px !important}/*Clearance*/
	
	.table thead th:nth-child(12){min-width:180px !important}/*Style*/
	.table tbody td:nth-child(12){min-width:180px !important}/*Style*/
	
	
	.table thead th:nth-child(13){min-width:80px !important}/*Color*/
	.table tbody td:nth-child(13){min-width:80px !important}/*Color*/
	
	.table thead th:nth-child(14){min-width:50px !important}/*Size*/
	.table tbody td:nth-child(14){min-width:50px !important}/*Size*/
	</style>
    
SEGDTA;
 
			$exporturl = $this->loadglobal.'exportexcel.php';
			$iecsspath = $this->loadglobal.'iecssTest.css';			
  	
		echo <<<SEGDTA

  	
	 <link type="text/css" rel="stylesheet" href="
SEGDTA;
 echo $iecsspath;
		echo <<<SEGDTA
" />	
    
    <script type="text/javascript"> 
      
    $(document).ready(function() 
    {  
    	$("#ww_fARGSTYL").val(0);  
		$(document).on('click', '#ww_fARGSTYL', function() {
			AggrChecked();
		});	   
		$(document).on('click', '.exportButtonDsp', function() {
			onChange();
		});	 
	});
    
    
    /*-------Function encode & Decode-----------*/   
	    var Base64 = { 
	  // private property
	  _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	  // public method for decoding
	  decode : function (input) {
	    var output = "";
	    var chr1, chr2, chr3;
	    var enc1, enc2, enc3, enc4;
	    var i = 0;
	 
	    input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
	 
	    while (i < input.length) {
	      enc1 = this._keyStr.indexOf(input.charAt(i++));
	      enc2 = this._keyStr.indexOf(input.charAt(i++));
	      enc3 = this._keyStr.indexOf(input.charAt(i++));
	      enc4 = this._keyStr.indexOf(input.charAt(i++));
	 
	      chr1 = (enc1 << 2) | (enc2 >> 4);
	      chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
	      chr3 = ((enc3 & 3) << 6) | enc4;
	 
	      output = output + String.fromCharCode(chr1);
	 
	      if (enc3 != 64) {
	        output = output + String.fromCharCode(chr2);
	      }
	      if (enc4 != 64) {
	        output = output + String.fromCharCode(chr3);
	      }
	    }
	    output = Base64._utf8_decode(output);
	    return output;
	  },
	
	   // private method for UTF-8 decoding
	  _utf8_decode : function (utftext) {
	    var string = "";
	    var i = 0;
	    var c = c1 = c2 = 0;
	 
	    while ( i < utftext.length ) { 
	      c = utftext.charCodeAt(i);
	 
	      if (c < 128) {
	        string += String.fromCharCode(c);
	        i++;
	      }
	      else if((c > 191) && (c < 224)) {
	        c2 = utftext.charCodeAt(i+1);
	        string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
	        i += 2;
	      }
	      else {
	        c2 = utftext.charCodeAt(i+1);
	        c3 = utftext.charCodeAt(i+2);
	        string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
	        i += 3;
	      }
	    }
	 
	    return string;
	  }
	}
	// Note: AED - add function header comments
	function AggrChecked()
	{		
		var checkbox = $('#ww_fARGSTYL:checked').length > 0;
		if (checkbox != false)
		{
			//checkbox checked			
			jQuery('#ww_fIDEPTX').val(["5", "6"]).trigger('chosen:updated');
			jQuery('#ww_fARGSTYL').val(1);
			
		}
		else{
			//checkbox uncheck	
			//jQuery('#ww_fIDEPTX').val(["0"]).trigger('chosen:updated');
			jQuery('#ww_fARGSTYL').val(0);
		}
	}
	// Note: AED - add function header comments
	function aggrSKUDisp(STORE,SKU,DEPT,STYLE,TRANSDATE)
	{				
		var pgurl = '{$this->pgurl}';
		var pasurl = pgurl+"/wsphp/AGGRSKU.php?STORE="+ STORE +"&SKU="+SKU+"&DEPT="+DEPT+"&STYLE="+STYLE+"&FDATE="+TRANSDATE;
		var w=1300;
		var h=370;
		var left = (screen.width/2)-(w/2);
		var top = (screen.height/2)-(h/2);
		
	    window.open(pasurl,'mywindow','titlebar=0,status=0,toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=0,resizable=0,copyhistory=0,width='+w+', height='+h+', top='+top+', left='+left);
	    //return;
	     
	}
	
	// called when filters are changed
	function onChange() 
	{
		jQuery('#flexport').attr('disabled','disabled');
		
		jQuery('#btn_filter').removeAttr('disabled');		
	}
	// Note: AED - add function header comments
	function onLoad(success)
	{
		if ($(".dataTable tr:has(td.dataTables_empty)").length > 0) 
		{
		    jQuery('#flexport').attr('disabled','disabled');
		}
		else
		{
			 jQuery('#flexport').removeAttr('disabled');
		}
		jQuery('#btn_filter').attr('disabled','disabled');
	}
	// Note: AED - add function header comments
   function FunErrrMsgDisp(haserr,dismsg,txtbxid,txtbxsecid){
			if(haserr=='err'){
				jQuery('#'+txtbxid).addClass('alert-danger');
				if(txtbxsecid!=''){jQuery('#'+txtbxsecid).addClass('alert-danger');}
				jQuery('#display-message').addClass ('alert-danger');
				jQuery('#message').addClass ('alert-danger');
				jQuery('#message').html(dismsg);
				jQuery('.btn.filter').prop('disabled', 'disabled');
				jQuery('#message').css("visibility", "visible");	
				jQuery('#display-message').css("visibility", "visible");							
			}
			else{
				jQuery('#'+txtbxid).removeClass('alert-danger');
				if(txtbxsecid!=''){jQuery('#'+txtbxsecid).removeClass('alert-danger');}
				jQuery('#ww_fITRDAT_TOX').removeClass('alert-danger');
				jQuery('#display-message').removeClass ('alert-danger');
				jQuery('#message').removeClass ('alert-danger');
				jQuery('#message').html('');
				jQuery('.btn.filter').removeProp('disabled');
				jQuery('#message').css("disabled", "disabled");	
				jQuery('#display-message').css("disabled", "disabled");							
			}
		}
		// date validation function
		function isValidDate(DateVal,frmdt,todt)
		{				
				if (DateVal!="" && DateVal !="0") 
				{
					if (DateVal.length!= 10) 		{ return false; }
					if (DateVal.substr(2,1) !='/') 	{ return false;	}
					if (DateVal.substr(5,1) !='/' ) { return false;	}		
					if (DateVal.substr(0,2) > '12' || DateVal.substr(0,2) <= '00') 	{
						return false;
					}					
					if (DateVal.substr(3,2) > '31' || DateVal.substr(3,2) <= '00') 	{
						return false;
					}					
					if (DateVal.substr(6,4) > '2999' || DateVal.substr(6,4) <= '0000') {
						return false;
					}					
					if (Date.parse(DateVal) === NaN)
					{	return false;				}
					else
					{ 	return true;	 			}
				}
				else {	return true;	}
			};
		
   // DATE PICKERS	
	// Calendar lookup for field ID "ww_fITRDAT_FRMX"
   	//	var backdate = new Date()-"7D" ;
	jQuery(function() {						
		jQuery('#ww_fITRDAT_FRMX').datepicker({ 
	   		altFormat: "yy/mm/dd",
	   		defaultDate: "-7d", // highlight: "Yellow",
	   		minDate: "-7Y",
	   		maxDate: "+0D",
	   		numberOfMonths: 1,
	   		changeYear: true,
	   		changeMonth:true,
	   		onSelect: function(dateText, inst) {
			setTransdtFromDate(this.value);  
			var start = new Date($("#ww_fITRDAT_FRMX").val());
			var end = new Date($("#ww_fITRDAT_TOX").val());
		
			var age_year   = Math.floor((end - start)/31536000000);
			var age_month   = Math.floor(((end - start)% 31536000000)/2628000000);
			var age_day   = Math.floor((((end - start)% 31536000000) % 2628000000)/86400000);
			
			onChange();
			
			if (start > end ) {
				FunErrrMsgDisp('err','From Date is greater than To Date!','ww_fITRDAT_FRMX');//Function for error message
				return;
			}
			else if(age_year >= 2 && age_day>=1){
					FunErrrMsgDisp('err','Date range must not be more than 2 Years !','ww_fITRDAT_FRMX','ww_fITRDAT_TOX');//Function for error message
			}
			else{
				FunErrrMsgDisp('noerr','','ww_fITRDAT_FRMX','ww_fITRDAT_TOX');//Function for remove error message
			} 
		}
     	});    	
		      	
	      jQuery('#ww_fITRDAT_TOX').datepicker({ 
			   altFormat: "yy/mm/dd",
			   defaultDate: "+0d",	
			   minDate: "-7Y",
			   maxDate: "+0D",
		   	   numberOfMonths: 1,
			   changeYear: true,
			   changeMonth:true,
			   onSelect: function(dateText, inst) {
					setTransdtToDate(this.value);		  
					var from = new Date($("#ww_fITRDAT_FRMX").val());
					var to = new Date(dateText);
					var start = from;
					var end   = to;
					var age_year   = Math.floor((end - start)/31536000000);
					var age_month   = Math.floor(((end - start)% 31536000000)/2628000000);
					var age_day   = Math.floor((((end - start)% 31536000000) % 2628000000)/86400000);
				
					onChange();
				
					if (from > to) {
						FunErrrMsgDisp('err','From Date is greater than To Date!','ww_fITRDAT_FRMX','ww_fITRDAT_TOX');//Function for error message
						return;
					}
					else if(age_year >= 2 && age_day>=1){
						FunErrrMsgDisp('err','Date range must not be more than 2 Years !','ww_fITRDAT_FRMX','ww_fITRDAT_TOX');//Function for error message
					}
					else{
						FunErrrMsgDisp('noerr','','ww_fITRDAT_FRMX','ww_fITRDAT_TOX');//Function for no error message
					}					 
				}     
		   
	       });
		    
			// Note: AED - add function header comments              
            function setTransdtFromDate(selected)
			{ jQuery("#ww_fITRDAT_FRMX").val(selected); }
		
		    function setTransdtToDate(selected)
			{ jQuery("#ww_fITRDAT_TOX").val(selected);	}

		var oColumns = [
			{name: 'RANK', className: 'text-right',orderable:false},									       
			{name: 'INUMBR', className: 'text-right'},      
			{name: 'SKUDESCRIPTION', className: 'text-left'},      
			{name: 'IDEPT', className: 'text-right'},      
			{name: 'DESCRIPTION', className: 'text-left'}, 
			{name: 'PRICE', className: 'text-right'},     
			{name: 'RETAIL', className: 'text-right'},
			{name: 'QTYSOLD', className: 'text-right'},      
			{name: 'ONHAND', className: 'text-right'},
			{name: 'IBPOOQ', className: 'text-right'},
			{name: 'CLRANCESKU', className: 'text-center'},
			{name: 'ISTYLN', className: 'text-center'},
			{name: 'ISCOLR', className: 'text-center'},
			{name: 'ISSIZE', className: 'text-center'},
			];
		var DataTablesManager = new ws_DataTablesManager('$pf_scriptname', '$tableId', oColumns);
		var elTable = jQuery('#$tableId');
	
		var oTable = elTable.DataTable({
			// Customize the table layout
		//	dom: 'lprtip',
			dom: 'rt<"bottom"ip><"clear">',
			scrollX: true,
			pageLength: {$programState['listSize']},
			// Set the table to server side, and set up columns and ordering
			serverSide: true,
			language:{ processing: "<img src='/WebSmart/v11.2/FollettNightFall/images/squares.gif' style='width:40px;height 40px;'> Loading...",		 	},
			columns: oColumns,
			order: [[ {$programState['sortIndex']}, '{$programState['sortDir']}' ]],
			ajax: {
				url:  '$pf_scriptname',
				type: 'POST',
				data: DataTablesManager.tableData.bind(DataTablesManager)
			},
			beforeSend: function() { jQuery('#wait').show();},
			fnDrawCallback: function(){
				jQuery('#wait').hide();
				onLoad();
				if (oTable.page.info().pages <= 1)
				{
					sessionStorage.removeItem("Start");
				}
			},
			destory:true 
		});
		jQuery('.bottom').hide();
	
		// Set the table to at most the width of the page body
		jQuery(window).resize(function() {
			resizeTable();
		});
		elTable.on('column-sizing.dt', function() {
			resizeTable();
		});
		 
	// Note: AED - add function header comments
		function resizeTable() {
			var elTableWrapper = jQuery('#{$tableId}_wrapper');
			var nWidth = Math.min(elTable.outerWidth(), jQuery('body').width());
			elTableWrapper.width(nWidth);
		}
		
		
		// Setup filtering fields
		jQuery('#btn_filter').click(function() {
			jQuery('#wait').show();
		
		/*--------Validate From & To Date----------*/		
		var frmdt = jQuery('#ww_fITRDAT_FRMX').val();
		var frmdtconv = new Date(frmdt);
		var todt = jQuery('#ww_fITRDAT_TOX').val();
		var todtconv = new Date(todt);
		var flgerr='0';
		var today = new Date();//Today
		
		var age_year2   = Math.floor((todtconv - frmdtconv)/31536000000);
		var age_month2   = Math.floor(((todtconv - frmdtconv)% 31536000000)/2628000000);
		var age_day2   = Math.floor((((todtconv - frmdtconv)% 31536000000) % 2628000000)/86400000);


		//for 7yr validation
		var frmbk_year   = Math.floor((today - frmdtconv)/31536000000);
		var frmbk_month = Math.floor((((today - frmdtconv)% 31536000000) % 2628000000)/86400000);
		var tobk_year   = Math.floor((today - frmdtconv)/31536000000);
		var tobk_month = Math.floor((((today - frmdtconv)% 31536000000) % 2628000000)/86400000);
		
		if(frmdt == ''){
			flgerr = 1;
			FunErrrMsgDisp('err','From Date must have value!','ww_fITRDAT_FRMX');//Function for error message
		}
		else if(todt==''){
			flgerr = 1;
			FunErrrMsgDisp('err','To Date must have value!','ww_fITRDAT_TOX');//Function for error message
		}
		else if(isValidDate(frmdt)!=true){
			flgerr = 1;
			FunErrrMsgDisp('err','Invalid From Date. Date must be in MM/DD/YYYY format!','ww_fITRDAT_FRMX');//Function for error message	
		}
		else if(isValidDate(todt)!=true){
			flgerr = 1;
			FunErrrMsgDisp('err','Invalid To Date. Date must be in MM/DD/YYYY format!','ww_fITRDAT_TOX');//Function for error message		
		}
		else if(frmdtconv > todtconv ){
			flgerr = 1;
			FunErrrMsgDisp('err','From Date should be less than To Date!','ww_fITRDAT_FRMX');//Function for error message
		}
		else if (frmdtconv > today){
			flgerr = 1;
			FunErrrMsgDisp('err','From Date should not be greater than Today!','ww_fITRDAT_FRMX');//Function for error message
		}
		else if(todtconv > today)
		{
			flgerr = 1;
			FunErrrMsgDisp('err','To Date should not be greater than Today!','ww_fITRDAT_TOX');//Function for error message
		}
		else if(age_year2 >= 2 && age_day2>=1){
			flgerr = 1;	
			FunErrrMsgDisp('err','Date range must not be more than 2 Years !','ww_fITRDAT_FRMX','ww_fITRDAT_TOX');//Function for error message
		}
		else if(frmbk_year > 7 && frmbk_month>=1){
			flgerr = 1;	
			FunErrrMsgDisp('err','From Date should not be less than 7 Years from Today!','ww_fITRDAT_FRMX');//Function for error message	
		}
		else if(tobk_year > 7 && tobk_month>=1){
			flgerr = 1;	
			FunErrrMsgDisp('err','To Date should not be less  than 7 Years from Today!','ww_fITRDAT_TOX');//Function for error message	
		}
		
		if(flgerr!=1)
		{
			FunErrrMsgDisp('noerr','','ww_fITRDAT_FRMX','ww_fITRDAT_TOX');//Function for no error message	
	
		//Validate date
		// Data in qtysold/retail order
      	jQuery('#ww_fTYPE').val(jQuery("input[name=optradio]:checked").val());     		
		
		//How many rows of data is required
		  jQuery('#ww_fTOP').val(jQuery("#ww_fTOPX option:selected").val());
		
		// Department - Multiple select departments
		
			var multipleSelectedValues= 0;
			jQuery('#ww_fIDEPTX :selected').each(function(i, sel){ 
				if(multipleSelectedValues==0)
				{
					multipleSelectedValues =  $(sel).val() ;
				}
				else
				{
					multipleSelectedValues += "," +  $(sel).val() ; 		  	 
				}
			});
					
		 //sending ITRDAT by concatinating from and to dates		    
		    var transdt = jQuery('#ww_fITRDAT_FRMX').val()+'-'+jQuery('#ww_fITRDAT_TOX').val();
		    if (transdt == '-') 
		    {   	transdt = '';    }
			else
			{		transdt = transdt;		}		 	
		
		 	jQuery('#ww_fITRDAT').val(transdt);		 	
		 	
		
		/*-----Concat All value and send to this--------*/
   			if(jQuery("#Store").val()!='' || jQuery("#Store").val()!=null){
				var srcstore = jQuery("#Store").val();
			}
			else{
				var srcstore = 7;
			}
			
			if(jQuery("#ww_fITRDAT_FRMX").val()!='' || jQuery("#ww_fITRDAT_FRMX").val()!=null){
				var srcfrmdt = jQuery("#ww_fITRDAT_FRMX").val();
			}
			else{
				var srcfrmdt = 0;
			}
			if(jQuery("#ww_fITRDAT_TOX").val()!='' || jQuery("#ww_fITRDAT_TOX").val()!=null){
				var srcstodt = jQuery("#ww_fITRDAT_TOX").val();
			}
			else{
				var srcstodt = 0;
			}		
					
			if(jQuery("#ww_fIDEPTX option:selected").val()!='' || jQuery("#ww_fIDEPTX option:selected").val()!=null || jQuery("#ww_fIDEPTX option:selected").val()!='undefined'){
			//	var srcsdept = jQuery("#ww_fIDEPTX option:selected").val();
				var srcsdept=multipleSelectedValues;
			}
			else{
				var srcsdept = dptfv = dptsx = 0;
			}
			if(jQuery("input[name=optradio]:checked").val()!='' || jQuery("input[name=optradio]:checked").val()!=null){
				var srcstype = jQuery("input[name=optradio]:checked").val();
			}
			else{
				var srcstype =0;
			}
			if(jQuery("#ww_fTOPX option:selected").val()!='' || jQuery("#ww_fTOPX option:selected").val()!=null){
				var srcstop = jQuery("#ww_fTOPX option:selected").val();
			}
			else{
				var srcstop =0;
			}
			
			if(jQuery("#ww_fITRCLRSKU").is(':checked')) { var chkclrsku = 'Y';} else {var chkclrsku =  'N';}
			
			/*-----------Aggregate checkbox coding------------------*/			
			if(srcsdept!=''){				
				var dptfv = srcsdept.includes("5");
				var dptsx = srcsdept.includes("6");
				
				if(dptfv == true || dptsx == true){
					if(jQuery("#ww_fARGSTYL").is(':checked')) { var agrstyle = '1';} else {var agrstyle = '0';}
				}
				else{
					jQuery("#ww_fARGSTYL").attr('checked', false);
					var agrstyle = '0';	
				}			
			}
			else{
				jQuery("#ww_fARGSTYL").attr('checked', false);
				var agrstyle = '0';
			}			
			
			//store-fromdt-todat-dept-type-top
			var varonhan = srcstore+'-'+srcfrmdt+'-'+srcstodt+'-'+srcsdept+'-'+srcstype+'-'+srcstop+'-'+chkclrsku+'-'+agrstyle;
			
			jQuery("#ww_fIDEPT").val(varonhan); 
			jQuery("#passpara").val(varonhan);		
			
	
		 	DataTablesManager.filterTable();
		 	oTable.order([ 0, 'desc' ]);		 
		 	oTable.clear().draw();
		 	
		 	return false;
	}//End flag condition			
		});
		
		});
			
	</script>
	<!-- Load JS and CSS for Datepicker -->	
    <script type="text/javascript" src="/websmart/v11.2/js/jquery-ui.datepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/websmart/v11.2/css/jquery-ui.datepicker.min.css"/>    
    <script type="text/javascript">  
    
	// Note: AED - add function header comments
    function getUrlParameter(name) {
	    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
	    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
	    var results = regex.exec(location.search);
	    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
	};  		
			$(document).keypress(function(event){
				if(event.keyCode == 13){
					jQuery("#btn_filter").click();
				} 
			});
			jQuery(document).ready( function() {
				
				var gtstr = getUrlParameter('Store');

				var newurlstr = '
SEGDTA;
 echo $this->loadglobal;
		echo <<<SEGDTA
getSessionStore.php';	
			    if(gtstr!='')
			    {
			    	var ursltr = Base64.decode(gtstr);
			    	var gstrdec = ursltr.trim();
			    	if(isNaN(gstrdec))
			    	{
				    	window.location.href = newurlstr;	
					 }
					 else
					 {
					 	var Store = gstrdec;
						jQuery('#Store').attr('value',Store);			       			    	
					 }
			    }
			    else
			    {			    	
			    	window.location.href = newurlstr;	
			    }
				
				
		
				/*This resolve export issue after sorting*/
				jQuery('.dataTable  tr th').click(function () {
					jQuery('#expotask').attr('value','loading');
				});
				jQuery('#btn_filter').click(function () {
					jQuery('#expotask').attr('value','filter');
				});
				/*End This resolve export issue after sorting*/
		
				jQuery(".chosen-select").chosen();	
				if(jQuery('#Store').val()!= '' )
		        {
		        	jQuery('#ww_fITRLOC').val(jQuery('#Store').val());
		        }
				jQuery('#ww_fTOPX').on('change', function() 
		      	{
					onChange();
		       		 jQuery('#ww_fTOP').val( this.value);
		       	}); 
		       	
				jQuery('#ww_fIDEPTX').on('change', function() 
				{
					onChange();
		      		jQuery('#ww_fIDEPT').val( this.value);
		      	}); 
		      	
		      jQuery("#ww_fITRDAT_FRMX").on('blur', function() {
		      	
		      	var input=$(this);
				var is_from=input.val();
				var frmdtconv = new Date(is_from);
				var todt = jQuery('#ww_fITRDAT_TOX').val();
				var todtconv = new Date(todt);
				var today = new Date();//Today
				
				var age_year2   = Math.floor((todtconv - frmdtconv)/31536000000);
				var age_month2   = Math.floor(((todtconv - frmdtconv)% 31536000000)/2628000000);
				var age_day2   = Math.floor((((todtconv - frmdtconv)% 31536000000) % 2628000000)/86400000);
		
		
				//for 7yr validation
				var frmbk_year   = Math.floor((today - frmdtconv)/31536000000);
				var frmbk_month = Math.floor((((today - frmdtconv)% 31536000000) % 2628000000)/86400000);
			
			
					if( is_from =='')
					{
						FunErrrMsgDisp('err','From Date should not be blank!','ww_fITRDAT_FRMX');//Function for error message	
					}
					else if(isValidDate(is_from)!=true)
					{
						FunErrrMsgDisp('err','Invalid From Date. Date must be in MM/DD/YYYY format!','ww_fITRDAT_FRMX');//Function for error message	
					}  
					else if (frmdtconv > today){
						FunErrrMsgDisp('err','From Date should not be greater than Today!','ww_fITRDAT_FRMX');//Function for error message
					}
					else if(age_year2 >= 2 && age_day2>=1){
						FunErrrMsgDisp('err','Date range must not be more than 2 Years !','ww_fITRDAT_FRMX','ww_fITRDAT_TOX');//Function for error message
					}
					else if(frmbk_year > 7 && frmbk_month>=1){
						FunErrrMsgDisp('err','From Date should not be less than 7 Years from Today!','ww_fITRDAT_FRMX');//Function for error message	
					}
					
					else
					{
						FunErrrMsgDisp('noerr','!','ww_fITRDAT_FRMX','ww_fITRDAT_TOX');//Function for error message	
		          	}				
				});
				
			 jQuery("#ww_fITRDAT_TOX").on('blur', function() {
			 		var input=$(this);
					var is_to=input.val();
					var frmdt = jQuery("#ww_fITRDAT_FRMX").val();	
					var frmdtconv = new Date(frmdt);
					var todtconv = new Date(is_to);
					var today = new Date();//Today
					
					var age_year2   = Math.floor((todtconv - frmdtconv)/31536000000);
					var age_month2   = Math.floor(((todtconv - frmdtconv)% 31536000000)/2628000000);
					var age_day2   = Math.floor((((todtconv - frmdtconv)% 31536000000) % 2628000000)/86400000);
			
			
					//for 7yr validation
					var tobk_year   = Math.floor((today - todtconv)/31536000000);
					var tobk_month = Math.floor((((today - todtconv)% 31536000000) % 2628000000)/86400000);
			
		
					if(is_to=='')
					{
						FunErrrMsgDisp('err','To Date should not be blank!','ww_fITRDAT_TOX');//Function for error message
					}
					else if(isValidDate(is_to)!=true)
					{
						FunErrrMsgDisp('err','Invalid To Date. Date must be in MM/DD/YYYY format!','ww_fITRDAT_TOX');//Function for error message
					} 
					else if (todtconv > today){
						FunErrrMsgDisp('err','To Date should not be greater than Today!','ww_fITRDAT_TOX');//Function for error message
					}
					else if(age_year2 >= 2 && age_day2>=1){
						FunErrrMsgDisp('err','Date range must not be more than 2 Years !','ww_fITRDAT_FRMX','ww_fITRDAT_TOX');//Function for error message
					}
					else if(tobk_year > 7 && tobk_year>=1){
						FunErrrMsgDisp('err','To Date should not be less  than 7 Years from Today!','ww_fITRDAT_TOX');//Function for error message	
					}
					else
					{
						FunErrrMsgDisp('noerr','','ww_fITRDAT_FRMX','ww_fITRDAT_TOX');//Function for error message
		          	}				
				});
		      	 
			// to get the colour effect of mouse enter and leave effect
		  
		  	jQuery('#TopSoldSKU').on( "mouseenter", "tr", function(){
		
		  		jQuery(this).addClass('selected');
		  	
		  		});
			
		  	jQuery('#TopSoldSKU').on( "mouseleave", "tr", function(){
		 
		   		jQuery(this).removeClass('selected');
		  		});	
		  		
		  		
		  		
		  		jQuery('#btn_clr').click(function() {		  			
					 
					jQuery('#ww_fIDEPTX').chosen('destroy');
					jQuery('#ww_fIDEPTX').prop("selectedIndex", -1);
					jQuery('#ww_fIDEPTX').chosen();
					jQuery("#ww_fTOPX").val(jQuery("#ww_fTOPX option").eq(1).val());					
					
					jQuery("#ww_fTYPEX").prop("checked", true);					
					
					jQuery("#ww_fITRCLRSKU,#ww_fARGSTYL,#ww_fITRDAT_FRMX,#ww_fITRDAT_TOX").val('');					
					jQuery('#ww_fARGSTYL,#ww_fITRCLRSKU,#ww_fTYPEX1').prop('checked', false);
					
					console.log(jQuery('#txtfrmdtbfr').val());
		  		
					jQuery('#ww_fITRDAT_FRMX').val(jQuery('#txtfrmdtbfr').val());
					//jQuery('#ww_fITRDAT_FRMX').datepicker("option",{defaultDate: "-7d",minDate: "-7Y",maxDate: "+0D"});	
					
								
					jQuery('#ww_fITRDAT_TOX').val(jQuery('#txttodtbfr').val());
					//jQuery('#ww_fITRDAT_TOX').datepicker("option",{defaultDate: "+0d",minDate: "-7Y",maxDate: "+0D"});
					
					jQuery('#btn_filter').click();	 
	    		});
    			  		
		  	});//End Document Ready 
		
		// Note: AED - add function header comments
		function funcExport(){
			var allbind = jQuery('#passpara').val();
			var fields = allbind.split('-');
			
			jQuery("#exportStore").attr("value",fields[0]);	
			jQuery("#Transactdfrmdate").attr("value",fields[1]);
			jQuery("#Transactdtodate").attr("value",fields[2]);
			jQuery("#dept").attr("value",fields[3]);
			jQuery("#reportorder").attr("value",fields[4]);
			jQuery("#topnumrows").attr("value",fields[5]);
			jQuery("#expoclearancesku").attr("value",fields[6]);			
			jQuery("#expaggrsku").attr("value",fields[7]);
			
			var headrow = $('.dataTable thead tr ').children();
			$('.dataTable thead tr ').each(function(){
			  var row = $(this).children();
			  for(var i=0; i<headrow.length; ++i){
			      if($(headrow[i]).hasClass("sorting_desc")){
			        
			         if($(row[i]).text()!=''){
			         		jQuery("#exposortIndex").attr("value",$(row[i]).text());
			         		jQuery("#exposortDir").attr("value",'desc');
			         }
			      }
			      if($(headrow[i]).hasClass("sorting_asc")){
			         
			           if($(row[i]).text()!=''){
			         		jQuery("#exposortIndex").attr("value",$(row[i]).text());
			         		jQuery("#exposortDir").attr("value",'asc');
			           }
			      }
			  }
			});
					
			document.getElementById("frmexport").submit();			
		}
	</script>
  </head>
  <body class="display-list">
 
   <form name="frmexport" id="frmexport" action="
SEGDTA;
 echo $exporturl;
		echo <<<SEGDTA
" method="POST">  
  	<input type="hidden" name="exportStore" id="exportStore" value="">	
	<input type="hidden" name="Transactdfrmdate" id="Transactdfrmdate" value="">
	<input type="hidden" name="Transactdtodate" id="Transactdtodate" value="">
	<input type="hidden" name="dept" id="dept" value="">
	<input type="hidden" name="reportorder" id="reportorder" value="">
	<input type="hidden" name="topnumrows" id="topnumrows" value="">
	<input type="hidden" name="exposortIndex" id="exposortIndex" value="">
	<input type="hidden" name="exposortDir" id="exposortDir" value="">
	<input type="hidden" name="expotask" id="expotask" value="">
	<input type="hidden" name="expoclearancesku" id="expoclearancesku" value="">
	<input type="hidden" name="expaggrsku" id="expaggrsku" value="">		
	
	<input type="hidden" name="passpara" id="passpara" value="
SEGDTA;
 
		echo trim($this->programState['filters']['ITRLOC'    ]) . '-';
		echo trim($this->programState['filters']['ITRDAT'    ]) . '-';
		echo trim($this->programState['filters']['IDEPT'     ]) . '-';
		echo trim($this->programState['filters']['TYPE'      ]) . '-';
		echo trim($this->programState['filters']['TOP'       ]) . '-';
		echo trim($this->programState['filters']['CLRANCESKU']) . '-';
		
		echo <<<SEGDTA
">
	
	<input type="hidden" name="queryexcl" id="queryexcl" value="">
	<input type="hidden" name="exportfile" id="exportfile" value="Export">
	</form>
    <div id="outer-content">
      <div id="page-title-block" class="page-title-block">
        <img class="page-title-image" src="/websmart/v11.0/Arctic/images/company-logo.png">
        <div id="page-divider-top" class="page-divider"></div>
      </div>
      <div id="page-content">
        <div id="content-header" "font-weight:900">
          <h1 class="text-center"  >TOP SOLD SKU RANKING INQUIRY</h1>
          <div class="alert-container"></div>
        </div>
        <div class="clearfix"></div>
        <div id="contents">
          <form class="container-fluid" id="filter-form" action="$pf_scriptname"> 
           <input type="hidden" name="Store" id="Store" value="" />
            <input type="hidden" name="task" id="task" value="filter" />
            
            <div class="form">
              <div class="row">
              
               	     <div class="row filter-group form-group" style="display:block;margin-bottom:25px;">
               	     
               	     	<div class="col-sm-2 col-lg-2">
               	     		<label for="ww_fITRDAT_FRMX">Transaction From Date</label>
               	     		<input id="ww_fITRDAT_FRMX"  class="form-control" data-toggle="tooltip" data-placement="top" type="text" value="
SEGDTA;
 
					  		if ($this->programState['filters']['ITRDAT']) 
					  		{ echo substr($this->programState['filters']['ITRDAT'], 0, 10); } 
					  		else 
					  		{ echo date("m/d/Y", strtotime('-7 days'));	} 
		echo <<<SEGDTA
" style = "width:80%" />
					  		<input type="hidden" id="txtfrmdtbfr" value="
SEGDTA;
 echo date("m/d/Y", strtotime('-7 days'));
		echo <<<SEGDTA
">
               	     	</div>
               	     	
               	     	
               	     	<div class="col-sm-2 col-lg-2">
               	     		<label for="ww_fITRDAT_TOX" >To</label>
               	     		<input id="ww_fITRDAT_TOX"  class="form-control" data-toggle="tooltip" data-placement="top" type="text" value="
SEGDTA;

							if ($this->programState['filters']['ITRDAT'])
							{echo substr($this->programState['filters']['ITRDAT'], 11, 10);	} 
					  		else { echo date("m/d/Y");	} 
		echo <<<SEGDTA
" style = "width:80%" />
							 <input type="hidden" id="txttodtbfr" value="
SEGDTA;
	echo date("m/d/Y");
		echo <<<SEGDTA
">
               	      </div>
			 
			 		 	<div class="col-sm-3 col-lg-3">
			 		 		<label for="ww_fIDEPTX" >Department</label>
			 		 		<select class="form-control chosen-select" id="ww_fIDEPTX" multiple data-placeholder="All Departments"> 
	                  			<option value='1'>1</option>
								<option value='2'>2</option>
								<option value='3'>3</option>
								<option value='4'>4</option>
								<option value='5'>5</option>
								<option value='6'>6</option>
								<option value='7'>7</option>
								<option value='8'>8</option>
								<option value='9'>9</option>
							</select>
			 		 	</div>
					             
                    <div class="col-sm-2 col-lg-2">
                    	<div style="overflow: hidden;display: block;margin-top: 11%;">
							<label for="ww_fITRCLRSKU" style="float:left;">Exclude Clearance SKUs?</label>
							<input id="ww_fITRCLRSKU"  class="exportButtonDsp form-control" data-toggle="tooltip" data-placement="top" type="checkbox" value="" style="width:20%;height:20px;box-shadow:none;float:left;margin-top:0px;" />
						</div>
					</div>
									
                </div>
                
              <div class="row filter-group form-group" style="display:block">
					<div class="col-sm-2 col-lg-2">
						<label for="ww_fTYPEX" style="display:block">Report Order</label>
						<label class="radio-inline" style="margin-bottom:5px;"><input class="exportButtonDsp" type="radio" name="optradio" id= "ww_fTYPEX" value="Q" checked>Quantity Sold</label>
						<label class="radio-inline" style="margin-bottom:5px;"><input class="exportButtonDsp" type="radio" name="optradio" id= "ww_fTYPEX1" value="R">Retail</label>
					</div>
					
					<div class="col-sm-2 col-lg-2">
						<div style="overflow:hidden;margin-top: 15px;">
							<label for="ww_fTYPEX" >Aggregate style</label>
							<label class="radio-inline" style="margin-bottom:5px;"><input class="exportButtonDsp" type="checkbox" name="aggregatestyle" id= "ww_fARGSTYL" value="1" style="width:20px;height:20px;"></label>					
						</div>
					</div>

					<div class="col-sm-1 col-lg-1">
						<label for="ww_fTOPX" >Top # of Rows </label>
						<select class="form-control " id="ww_fTOPX" >  
							<option value='50'>50</option>
							<option value='100' selected>100</option>
							<option value='150'>150</option>
							<option value='200'>200</option>
						</select>
					</div>

					<div class="col-sm-3 col-lg-3">
						<div style="margin-left:11%;overflow:hidden;margin-top:2%">
							<input id="btn_filter" type="submit" class="btn btn-primary filter" value="Display Results" title="Filter Content"  style="height:20%; margin-top: 15px;float:left"/>
							<input id="btn_clr" type="button" class="btn btn-primary clear" value="Clear" title="Clear Content" style="float:left;margin-top: 15px;margin-left:3%;width:25%"/>
							&nbsp;&nbsp;
							<div style="float:right;padding-left: 5%;margin-top:15px"><span id="flexport" onclick="javascript:funcExport();" style="background:#337ab7;color:#fff;border-radius: 3px;" disabled="disabled" class="btn btn-primary">Export Excel</span></div>
						</div>
					</div>
             </div>          
            <div class = "hidden">                
              <input id="ww_fITRDAT" class="form-control" type="hidden" name="ITRDAT"/>
              <input id="ww_fTOP" class="form-control" type="hidden" name="TOP" />
              <input id="ww_fTYPE" class="form-control" type="hidden" name="TYPE"/>
               <input id="ww_fIDEPT" class="form-control" type="text" name="IDEPT" />
              <input id="ww_fITRLOC" class="form-control" type="text" name="ITRLOC"/>
            </div>
            
          </form>
          
          <div class="clearfix"></div>
          <div id="display-message" style="padding:5px 5px 5px 15px;">
            <span id="message" class="ui-corner-all">&nbsp;</span>
          </div>
          <div id="wait"><img src="
SEGDTA;
 echo $this->loadglobal;
		echo <<<SEGDTA
loading.gif" title=">Loading..." alt=">Loading..."></div>
          <table id="$tableId" class="dataTable table table-striped table-bordered" width="100%">
            <thead>
              <tr> 
              	<th>Rank</th>
              	<th>SKU</th>
                <th>SKU Description</th>
                <th>Department </th>
                <th>Department Name</th>                
                <th>Curent Price</th>
                <th>Retail</th>
                <th>Qty Sold/Rented</th>
                <th>MMS On Hand</th> 
                <th>PO On Order</th> 
                <th>Clearance?</th>
                <th>Style</th> 
                <th>Color</th> 
                <th>Size</th>                           
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>   
  </body>
</html>

SEGDTA;
		return;
	}

		// If we reach here, the segment is not found
		echo("Segment $xlSegmentToWrite is not defined! ");
	}

	// return a segment's content instead of outputting it to the browser
	function getSegment($xlSegmentToWrite, $segmentVars=array())
	{
		ob_start();
		
		$this->writeSegment($xlSegmentToWrite, $segmentVars);
		
		return ob_get_clean();
	}
	
	function __construct()
	{
		parent::__construct();

		$this->pf_scriptname = 'TopSoldSKU.php';
		$this->pf_wcm_set = '';
		
		
		$this->pf_liblLibs[1] = 'MM4R6LIB';
		$this->xl_set_env($this->pf_wcm_set);
		
		// Last Generated CRC: 5813BF90 C872C53D AB3CD5B6 2CFFFC17
		// Last Generated Date: 2018-03-22 15:00:15
		// Path: R:\MMS Dev\MMS IMS\Experiments\Offshore\Gouri\TopSoldSKU.phw
	}
}

// Auto-load this WebSmart object (by calling xlLoadWebSmartObject) if this script is called directly (not via an include/require).
// Comment this line out if you do not wish this object to be invoked directly.
xlLoadWebSmartObject(__FILE__, 'TopSoldSKU');?>
