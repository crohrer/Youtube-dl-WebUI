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
                <button class="nav-link active" id="nav-list-tab" data-bs-toggle="tab" data-bs-target="#nav-list" type="button" role="tab" aria-controls="nav-list" aria-selected="true">List</button>
                <button class="nav-link" id="nav-grid-tab" data-bs-toggle="tab" data-bs-target="#nav-grid" type="button" role="tab" aria-controls="nav-grid" aria-selected="false">Grid</button>
            </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-list" role="tabpanel" aria-labelledby="nav-list-tab" tabindex="0">
                    <br>
                    <h2>List of available files:</h2>
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
                        foreach($files as $f)
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
                </div>
                <div class="tab-pane fade" id="nav-grid" role="tabpanel" aria-labelledby="nav-grid-tab" tabindex="0">
                    <br>
                    <div class="row">
                        <?php
                            foreach($files as $f)
                            {
                                echo '<div class="col-sm-3 mb-3 mb-sm-0"><div class="card">';
                                if ($file->get_relative_downloads_folder())
                                {
                                    echo "<video controls styles='outline: none;' class='embed-responsive-item'><source src=\"".rawurlencode($file->get_relative_downloads_folder()).'/'.rawurlencode($f["name"])."\"></source></video><div>".$f["name"]."</div>";
                                }
                                else
                                {
                                    echo "<td>".$f["name"]."</td>";
                                }
                                echo "<td>".$f["size"]."</td>";
                                echo "<td><a class='button' href=\"./list.php?delete=".sha1($f["name"])."\" class=\"btn btn-danger btn-sm pull-right\">Delete</a></td>";
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
