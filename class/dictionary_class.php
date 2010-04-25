<?php
//////////////////////////////////////////// Dictionary class is written by Reza Salehi. ////////////////////////////////////////////
//////////////////////////////////////////// General Public License.					 ////////////////////////////////////////////
//////////////////////////////////////////// zaalion@yahoo.com, http://zaalion.com		 ////////////////////////////////////////////
//////////////////////////////////////////// tel : +98 912 2345463						 ////////////////////////////////////////////
//////////////////////////////////////////// Dec. 2004.									 ////////////////////////////////////////////

class hashing
{
        function hashFunction($str, $len)
        {
                $hash=0;
                $l = strlen($str);
				
                //step 1
                for($i=0; $i<$l; $i++)                
                        $hash+=ord($str[$i])*($i+1)*($i+1);
                
                $hash*=ord($str[0]);
                //step 2
                $hash%=$len;
				 
                return($hash); 
        }
};

class lookupTable
{
		var $MAX=6967;//6967
		var $MaxWordLength=45;
		var $dictionaryFile="dictionary.txt";
		var $lookupTable;
		var $MAXLen=16;
		
		function initializeArray()
		{
			for($i=0; $i<$this->MAX; $i++)					
				for($j=0; $j<$this->MAXLen; $j++)						
					$this->lookupTable[$i][$j]=NULL;									
		} 


		function getLength()
		{
			return($this->MAX);
		}
		
		function loadFactor()
		{
				return($this->getDirectorySize()/$this->MAX+1);
		}

		function getDirectorySize()
		{
			   $buffer="";
			   $fp = fopen ("dictionary.txt", "r");
			   $count=0;
			   
			   while(strlen(fgets($fp, $this->MaxWordLength)))
						$count++;
						
			   fclose($fp);
			   return($count);
		}
		
		function longestChain()
		{
				$longest=0;
				$current=0;
				$j=0;
				
				for($i=0; $i<$this->MAX; $i++)
				{
					while($this->lookupTable[$i][$j++]!=NULL)
						$current++;
					if($current>$longest)
						$longest=$current;
						
					$current=0;
					$j=0;						
				}
				
				return($longest);
		}

		function shortestChain()
		{
				$shortest=$this->$MAXLen;
				$current=0;
				$j=0;
				
				for($i=0; $i<$this->MAX; $i++)
				{
					while($this->lookupTable[$i][$j++]!=NULL)
						$current++;
					if($current<$shortest)
						$shortest=$current;
						
					$current=0;
					$j=0;						
				}
				
				return($shortest);
		}
					   
		function readToTable()
		{
				$this->initializeArray();
				
				$buffer=""; $n=0;
				$hash=new hashing();
				$index=0;
				$buffer="";
				$count=0; $i=0;
				
				$fp = fopen ("dictionary.txt", "r");                      
				do
				{
						$buffer=fgets($fp, $this->MaxWordLength);
						$buffer=trim($buffer);
						
						$index=$hash->hashFunction($buffer, $this->MAX);
						//---->read process
						while($this->lookupTable[$index][$i]!=NULL) 
							$i++;
						$this->lookupTable[$index][$i]=$buffer;						
						$i=0;
						//<--- read process
				}
				while($buffer);
				
				fclose($fp);
		}

		function isIn($str)
		{
				$h=new hashing();
				$i=0;
				$str=trim($str);
				$temp="";
				
				$index=$h->hashFunction($str, $this->MAX);
				do
				{
					$temp=$this->lookupTable[$index][$i++];										
					if($temp==$str)
						return(true);
				}
				while($temp!=NULL);				
													
				return(false);
		}

		function suggestion($str)
		{
				$count=0;
				$index=-1;
				$current=97;
				$strC=$str;

				for($i=0; $i<strlen($strC); $i++)
				{
						for($j=0; $j<26; $j++)
						{
								$strC[$i]= chr($current++);
								if($this->isIn($strC))
								{
										if($count>0) print(" , ");
										print($strC);
										$count++;
								}
						}
						
						$strC=$str;
						$current=97;
				}
				return($count);
		}
} ;

class dataReader
{
		function reader($strToCheck, $sug, $stat)
		{
				$lt=new lookupTable();
				$lt->readToTable();

				$buffer="";
				$words=explode(' ', $strToCheck);
				$h=new hashing();

				if($stat)
				{
						print("<b>Lookup table properties : </b><br>");
						print("&nbsp;&nbsp;&nbsp;Dictionary file size : ".$lt->getDirectorySize()." words.<br>&nbsp;&nbsp;&nbsp;Dynamic Table length : ".$lt->getLength()."<br>");
						print("&nbsp;&nbsp;&nbsp;Longest chain : ".$lt->longestChain()." links. <br>&nbsp;&nbsp;&nbsp;loadfactor :  ".$lt->loadFactor()."<br><br>");
				}
				
				$count = 0;
				
				for($i=0; $i<count($words); $i++)
				{
						$buffer=$words[$i];						
						
						if(!($lt->isIn($buffer)))
						{
							$count++;								
						}
				}
				return $count;
		}
};

?>