<?php
	require_once 'class/Session.php';
	require_once 'class/Downloader.php';
	require_once 'class/FileHandler.php';

	$session = Session::getInstance();
	$file = new FileHandler;

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

	$files = $file->listFiles();
	$parts = $file->listParts();

	require 'views/header.php';
?>
		<div class="container my-4">
		<?php
			if(!empty($files))
			{
		?>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link" id="nav-list-tab" data-bs-toggle="tab" data-bs-target="#nav-list" type="button" role="tab" aria-controls="nav-list" aria-selected="true">List</button>
                <button class="nav-link active" id="nav-grid-tab" data-bs-toggle="tab" data-bs-target="#nav-grid" type="button" role="tab" aria-controls="nav-grid" aria-selected="false">Grid</button>
            </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show" id="nav-list" role="tabpanel" aria-labelledby="nav-list-tab" tabindex="0">
                    <br>
                    <h2>List of available files:</h2>
                    <table class="table table-striped table-hover table-dark">
                        <thead>
                            <tr>
                                <th>Thumbnail</th>
                                <th>Title</th>
                                <th>Quality</th>
                                <th>Duration</th>
                                <th>Size</th>
                                <th><span class="pull-right">Delete link</span></th>
                            </tr>
                        </thead>
                        <tbody>
                    <?php
                        foreach($files as $f)
                        {
                            echo "<tr>";
                            echo "<td><img width='150' src='".$f["meta"]->thumbnail."'/></td>";
                            if ($file->get_relative_downloads_folder())
                            {
                                echo "<td><a href=\"".rawurlencode($file->get_relative_downloads_folder()).'/'.rawurlencode($f["name"])."\">".$f["meta"]->title??$f["name"]."</a></td>";
                            }
                            else
                            {
                                echo "<td>".$f["name"]."</td>";
                            }
                            echo "<td>".$f["meta"]->height??"n/a"."</td>";
                            echo "<td>".$f["meta"]->duration_string??"n/a"."</td>";
                            echo "<td>".$f["size"]."</td>";
                            echo "<td><a href=\"./list.php?delete=".sha1($f["name"])."\" class=\"btn btn-danger btn-sm pull-right\">Delete</a></td>";
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
                                echo "<a style='position: relative;' href=\"".rawurlencode($file->get_relative_downloads_folder()).'/'.rawurlencode($f["name"])."\">";
                                echo "<img style='max-width: 100%;' src='".$f["meta"]->thumbnail."'/>";
                                echo "<div style='position: absolute; bottom: 0; right: 0; padding: 1px 3px; color: #fff; opacity: .8;' class='bg-secondary'>".$f["meta"]->duration_string."</div>";
                                echo "<div style='position: absolute; top: 0; right: 0; padding: 1px 3px; color: #fff; opacity: .9; size: .5em;' class='bg-secondary'>".$f["meta"]->height."p</div>";
                                echo "</a>";
                                echo "<div class='card-body'>";
                                echo "<a href=\"".rawurlencode($file->get_relative_downloads_folder()).'/'.rawurlencode($f["name"])."\">";
                                echo $f["meta"]->title;
                                echo "<div>".$f["size"]."</div>";
                                echo "</a>";
                                echo "<a class='btn btn-danger btn-sm' href=\"./list.php?delete=".sha1($f["name"])."\" class=\"btn btn-danger btn-sm pull-right\">Delete</a>";
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
			<table class="table table-striped table-hover ">
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
						echo "<td><a href=\"".rawurlencode($file->get_relative_downloads_folder()).'/'.rawurlencode($f["name"])."\" download>".$f["name"]."</a></td>";
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
