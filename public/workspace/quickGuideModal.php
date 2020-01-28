<div class="modal fade bs-modal in" id="guide" tabindex="-5" role="basic" aria-hidden="true" style="display: hide;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" onclick="closeModal()" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">
                    <span class="fa-stack fa-sm">
                        <i class="fa fa-circle-o"></i>
                        <i class="fa fa-circle-thin fa-stack-1x" style="position: absolute;top: 7px;"></i>
                    </span>
                    Quick Guide
                </h4>
            </div>
            <div class="modal-body table-responsive">
                <h4>Welcome to the VRE. For executing the a tool on the VRE you must follow the next steps: </h4>
                <?php
                if ($_SESSION['User']['Type'] == 3) {
                ?>
                    <ul>
                        <li>
                            <p>This workspace is not persistent. To save your data please<a class="btn green" href="./login.php"><i class="fa fa-sign-in"></i>Login</a> or copy
                                the URL shown in the <em>Restore Link</em> box. This <em>Restore Link</em> can be used to
                                restore the current session in the following 7 days.</p>
                        </li>
                        <hr>
                    </ul>
                <?php
                }
                ?>

                <ul>
                    <li>
                        <p>First of all, you need to have some input data. You can bring your own data or you can use
                            the example dataset provided by the VRE:</p>
                        <p></p>
                        <p><a class="btn green" href="./getdata/uploadForm.php"><i class="fa fa-upload"></i> Upload my
                                own data</a></p>
                        <p><a class="btn green" href="./getdata/sampleDataList.php" id="btn-sample"><i class="fa fa-database"></i> Import example dataset</a></p>
                        <p><a class="btn green" onclick="closeModal()"><i class="fa fa-times"></i> No thanks, I want a
                                clean workspace</a></p>
                        <p>For more information please visit the <a class="btn green" href="./help/tools.php"><i class="fa fa-info-circle"></i> Help</a> section.</p>
                    </li>
                    <li>
                        <p>Once the data is in the VRE workspace, you can launch one of the tools available:</p>
                        <p><a class="btn green" href="./home/"><i class="fa fa-rocket"></i> Launch Tool</a></p>
                    </li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn dark btn-outline" data-dismiss="modal" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function closeModal() {
        document.getElementById('guide').style.display = "none";
    }

    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function eraseCookie(name) {
        document.cookie = name + '=; Max-Age=-99999999;';
    }

    if (getCookie("guide") == null) {
        setCookie('guide', 'true', 1)
        document.getElementById('guide').style.display = "block";
    } else {
        document.getElementById('guide').style.display = "none";
    }
</script>