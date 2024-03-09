<!-- Add -->
<div class="modal fade" id="addnew1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title"><b>Scan QR Code to get Student ID</b></h4>
                <div class="modal-body">
            <div class= "iframe-container">
            <iframe src="http://192.168.0.106" width="480" height="320" frameborder="0" scrolling="no"></iframe>
         </div>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="student_add.php">
                <div class="form-group">
                    <label for="firstname" class="col-sm-3 control-label">Firstname</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="firstname" name="firstname" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="lastname" class="col-sm-3 control-label">Lastname</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="lastname" name="lastname" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="student_id" class="col-sm-3 control-label">Student ID</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="student_id" name="student_id" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="course" class="col-sm-3 control-label">Course</label>

                    <div class="col-sm-9">
                      <select class="form-control" id="course" name="course" required>
                        <option value="" selected>- Select -</option>
                        <?php
                          $sql = "SELECT * FROM course";
                          $query = $conn->query($sql);
                          while($row = $query->fetch_array()){
                            echo "
                              <option value='".$row['id']."'>".$row['code']."</option>
                            ";
                          }
                        ?>
                      </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="photo" class="col-sm-3 control-label">Photo</label>

                    <div class="col-sm-9">
                      <input type="file" id="photo" name="photo">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-primary btn-flat" name="add"><i class="fa fa-save"></i> Save</button>
              </form>
            </div>
        </div>
    </div>
</div>

<style>
        /* Center the iframe horizontally */
        .iframe-container {
            display: flex;
            justify-content: center;
        }

        /* Optional: Adjust the size of the iframe */
        iframe {
            width: 480px;
            height: 480px;
        }
    </style>

     