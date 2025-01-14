<?php
	require_once 'class/Session.php';
	require_once 'class/Downloader.php';
	require_once 'class/FileHandler.php';

	$session = Session::getInstance();
	$file = new FileHandler;
    $hideImages = $file->is_image_hiding_enabled();

	if(!$session->is_logged_in())
	{
		header("Location: login.php");
		exit;
	}

	if($session->is_logged_in() && isset($_GET["delete"]))
	{
		$file->delete($_GET["delete"]);
		header("Location: list.php");
		exit;
	}

	if($session->is_logged_in() && isset($_GET["move"]))
	{
		$file->move($_GET["move"]);
		header("Location: list.php");
		exit;
	}

	$files = $file->listFiles();
	$parts = $file->listParts();

	require 'views/header.php';
?>
		<div class="container my-4">
		<?php
			if(!empty($files))
			{
		?>
        <nav class="row">
            <div class="col-6">
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link" id="nav-list-tab" data-bs-toggle="tab" data-bs-target="#nav-list" type="button" role="tab" aria-controls="nav-list" aria-selected="true">List</button>
                    <button class="nav-link active" id="nav-grid-tab" data-bs-toggle="tab" data-bs-target="#nav-grid" type="button" role="tab" aria-controls="nav-grid" aria-selected="false">Grid</button>
                </div>
            </div>
            <div class="col-6">
                <form action="" style="width: 50%; margin-left:50%;">
                    <select class="form-select" name="sort" id="sort-select" onchange="this.parentElement.submit();">
                        <option value="">Newest</option>
                        <?php
                        foreach([
                            ["oldest","Oldest"],
                            ["longest","Longest"],
                            ["shortest","Shortest"],
                            ["biggest","Biggest"],
                            ["smallest","Smallest"],
                            ["a-z","A-Z"],
                            ["z-a","Z-A"],
                            ["internal","internal first"],
                            ["external","external first"],
                            ["random","random"],
                        ] as $option) {
                            echo "<option value='".$option[0]."' ". ((($_GET["sort"]??"")==$option[0])?"selected":"").">".$option[1]."</option>";
                        }?>
                    </select>
                </form>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show" id="nav-list" role="tabpanel" aria-labelledby="nav-list-tab" tabindex="0">
                <br>
                <h2>List of available files:</h2>
                <table class="table table-striped table-hover table-dark" style="table-layout:fixed;">
                    <thead>
                        <tr>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Quality</th>
                            <th>Duration</th>
                            <th>Size</th>
                            <th><span class="pull-right">Delete link</span></th>
                            <?php if($file->external_folder_exists()){?>
                                <th>Move</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                <?php
                    foreach($files as $f)
                    {
                        echo "<tr>";
                        echo "<td><img class='".($hideImages?'hiddenImg':'')."' width='150' src='".$f["path"].'/'.rawurlencode($f["thumb"])."'/></td>";
                        if ($f["path"])
                        {
                            echo "<td><a href=\"".$f["path"].'/'.rawurlencode($f["name"])."\">".$f["title"]??$f["name"]."</a></td>";
                        }
                        else
                        {
                            echo "<td>".$f["name"]."</td>";
                        }
                        echo "<td>".$f["description"]??"n/a"."</td>";
                        echo "<td>".$f["height"]??"n/a"."</td>";
                        echo "<td>".$f["duration_string"]??"n/a"."</td>";
                        echo "<td>".$f["size"]."</td>";
                        echo "<td><a href=\"./list.php?delete=".sha1($f["name"])."\" class=\"btn btn-danger btn-sm pull-right\">Delete</a></td>";
                        if($file->external_folder_exists()){
                            echo "<td><a href=\"./list.php?move=".sha1($f["name"])."\" class=\"btn btn-".($f["external"]?"secondary":"warning")." btn-sm\">Move to ".($f["external"]?"internal":"external")."</a></td>";
                        }
                        echo "</tr>";
                    }
                ?>
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade show active" id="nav-grid" role="tabpanel" aria-labelledby="nav-grid-tab" tabindex="0">
                <br>
                <div class="row">
                    <?php
                        foreach($files as $f)
                        {
                            echo '<div class="col-sm-3 mb-3"><div class="card" style="overflow: hidden;">';
                            echo "<a title=\"".$f["description"]."\" style='position: relative;' href=\"".$f["path"].'/'.rawurlencode($f["name"])."\">";
                            echo "<img class='".($hideImages?'hiddenImg':'')."' style='max-width: 100%;' src='".$f["path"].'/'.rawurlencode($f["thumb"])."'/>";
                            echo "<div style='position: absolute; bottom: 0; left: 0; padding: 1px 3px; color: #fff; opacity: .8;' class='bg-secondary'>".$f["duration_string"]."</div>";
                            echo "<div style='position: absolute; top: 0; left: 0; padding: 1px 3px; color: #fff; opacity: .9; size: .5em;' class='bg-secondary'>".$f["height"]."p</div>";
                            echo "</a>";
                            echo "<div class='card-body'>";
                            echo "<p class='card-text'>";
                            echo "<a href=\"".$f["path"].'/'.rawurlencode($f["name"])."\">";
                            echo $f["title"];
                            echo "</a>";
                            echo "</p>";
                            if($file->external_folder_exists()){
                                echo "<a href=\"./list.php?move=".sha1($f["name"])."\" class=\"btn btn-".($f["external"]?"secondary":"warning")." btn-sm\">Move to ".($f["external"]?"internal":"external")."</a> ";
                            }
                            echo "<a href=\"./list.php?delete=".sha1($f["name"])."\" class=\"btn btn-danger btn-sm\">Delete</a>";
                            echo "</div>";
                            echo "</div></div>";
                        }
                    ?>
                </div>
            </div>
        </div>
		<?php
			}
			else
			{
				echo "<br><div class=\"alert alert-warning\" role=\"alert\">No files!</div>";
			}
		?>
			<br/>
		<?php
			if(!empty($parts))
			{
		?>
			<h2>List of part files:</h2>
			<table class="table table-striped table-hover" style="table-layout:fixed;">
				<thead>
					<tr>
						<th>Title</th>
						<th>Size</th>
						<th><span class="pull-right">Delete link</span></th>
					</tr>
				</thead>
				<tbody>
			<?php
				foreach($parts as $f)
				{
					echo "<tr>";
					if ($file->get_relative_downloads_folder())
					{
						echo "<td><a href=\"".$f["path"].'/'.rawurlencode($f["name"])."\" download>".$f["name"]."</a></td>";
					}
					else
					{
						echo "<td>".$f["name"]."</td>";
					}
					echo "<td>".$f["size"]."</td>";
					echo "<td><a href=\"./list.php?delete=".sha1($f["name"])."\" class=\"btn btn-danger btn-sm pull-right\">Delete</a></td>";
					echo "</tr>";
				}
			?>
				</tbody>
			</table>
			<br/>
			<br/>
		<?php
			}
		?>
			<br/>
		</div><!-- End container -->
<?php
	require 'views/footer.php';
?>
