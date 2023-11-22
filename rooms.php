<section class="page-section bg-dark" id="home">
	<div class="container">
		<h2 class="text-center">Hostels List</h2>
	<div class="d-flex w-100 justify-content-center">
		<hr class="border-warning" style="border:3px solid" width="15%">
	</div>
	<div class="w-100">
		<?php
		$room = $conn->query("SELECT * FROM `room_list` order by rand() ");
			while($row = $room->fetch_assoc() ):
				$cover='';
				if(is_dir(base_app.'uploads/room_'.$row['id'])){
					$img = scandir(base_app.'uploads/room_'.$row['id']);
					$k = array_search('.',$img);
					if($k !== false)
						unset($img[$k]);
					$k = array_search('..',$img);
					if($k !== false)
						unset($img[$k]);
					$cover = isset($img[2]) ? 'uploads/room_'.$row['id'].'/'.$img[2] : "";
				}
				$row['description'] = strip_tags(stripslashes(html_entity_decode($row['description'])));
		?>
			<div class="card d-flex w-100 rounded-0 mb-3 package-item">
				<img class="card-img-top" src="<?php echo validate_image($cover) ?>" alt="<?php echo $row['room'] ?>" height="200rem" style="object-fit:cover">
				<div class="card-body">
				<h5 class="card-title truncate-1"><?php echo $row['room'] ?></h5>
				<p class="card-text truncate"><?php echo $row['description'] ?></p>
				<div class="w-100 d-flex justify-content-between">
					<span class="rounded-0 btn btn-flat btn-sm btn-primary"><i class="fa fa-tag"></i> <?php echo number_format($row['price']) ?></span>
					<a href="./?page=view_room&id=<?php echo md5($row['id']) ?>" class="btn btn-sm btn-flat btn-warning">View Hostels <i class="fa fa-arrow-right"></i></a>
				</div>
				</div>
			</div>
		<?php endwhile; ?>
	</div>
	
	</div>
</section>