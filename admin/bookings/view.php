<?php
include '../../config.php';
if(isset($_GET['id'])){
    $qry = $conn->query("SELECT b.*,r.room,r.description, concat(u.firstname,' ',u.lastname) as name,u.email,u.contact FROM `bookings` b inner join `room_list` r on r.id = b.room_id inner join users u on u.id = b.user_id where b.id = '{$_GET['id']}' ");
    foreach($qry->fetch_assoc() as $k => $v){
        $$k = $v;
    }
}
?>
<style>
    #uni_modal .modal-content>.modal-footer{
        display:none;
    }
</style>
<p><b>Client:</b> <?php echo ucwords($name) ?></p>
<p><b>Client Email:</b> <?php echo ($email) ?></p>
<p><b>Client Contact:</b> <?php echo ($contact) ?></p>
<p><b>Room:</b> <?php echo $room ?></p>
<p><b>Details:</b> <span class="truncate"><?php echo strip_tags(stripslashes(html_entity_decode($description))) ?></span></p>
<p><b>Schedule:</b> <?php echo date("F d, Y",strtotime($date_in)).' - '.date("F d, Y",strtotime($date_out)) ?></p>
<?php if(!empty($accommodation_ids)): ?>
<p><b>Additional Accommodation:</b>
<?php 
$accom = "";
$qry2 = $conn->query("SELECT * FROM accommodations where id in ({$accommodation_ids}) order by accommodation asc");
while($row= $qry2->fetch_assoc()):
    if(!empty($accom)) $accom .= ", ";
    $accom .= $row['accommodation'];
endwhile;
echo $accom;
?>
<?php ?>
</p>
<?php endif; ?>
<form action="" id="book-status">
    <input type="hidden" name="id" value="<?php echo $id ?>">
    <div class="form-group">
        <label for="" class="control-label">Status</label>
        <select name="status" id="" class="select custom-select">
            <option value="0" <?php echo $status == 0 ? "selected" : '' ?>>Pending</option>
            <option value="1" <?php echo $status == 1 ? "selected" : '' ?>>Approved</option>
            <option value="2" <?php echo $status == 2 ? "selected" : '' ?>>Cancelled</option>
            <option value="3" <?php echo $status == 3 ? "selected" : '' ?>>Done</option>
        </select>
    </div>
</form>

<div class="modal-footer">
<button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Update</button>
<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
</div>

<script>
    $(function(){
        $('#book-status').submit(function(e){
            e.preventDefault();
            start_loader()
            $.ajax({
                url:_base_url_+"classes/Master.php?f=update_book_status",
                method:"POST",
                data:$(this).serialize(),
                dataType:"json",
                error:err=>{
                    console.log(err)
                    alert_toast("an error occured",'error')
                    end_loader()
                },
                success:function(resp){
                    if(typeof resp == 'object' && resp.status == 'success'){
                        location.reload()
                    }else{
                        console.log(resp)
                        alert_toast("an error occured",'error')
                    }
                    end_loader()
                }
            })
        })
    })
</script>