<div class="form-group">
    <label class="col-md-4 control-label"><?php echo __("Name"); ?></label>
    <div class="col-md-8 inputGroupContainer">
        <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-pencil"></i></span>
            <input  id="inputName" placeholder="<?php echo __("Name"); ?>" class="form-control"  type="text" value="<?php echo $user->getName(); ?>" required >
        </div>
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label"><?php echo __("User"); ?></label>
    <div class="col-md-8 inputGroupContainer">
        <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
            <input  id="inputUser" placeholder="<?php echo !empty($advancedCustomUser->forceLoginToBeTheEmail) ? "me@example.com" : __("User"); ?>" class="form-control"  type="<?php echo empty($advancedCustomUser->forceLoginToBeTheEmail)?"text":"email"?>" value="<?php echo $user->getUser(); ?>" required <?php echo YouPHPTubePlugin::isEnabledByName("LoginLDAP")?"readonly":""; ?>  >
        </div>
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label"><?php echo __("E-mail"); ?></label>
    <div class="col-md-6 inputGroupContainer">
        <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
            <input  id="inputEmail" placeholder="<?php echo __("E-mail"); ?>" class="form-control"  type="email" value="<?php echo $user->getEmail(); ?>" required
                    <?php if (!empty($advancedCustomUser->forceLoginToBeTheEmail)) {
                        echo "readonly";
                    } ?>    >
        </div>
    </div>
    <div class="col-md-2">
        <?php
        if ($user->getEmailVerified()) {
            ?>
            <span class="btn btn-success"><i class="fa fa-check"></i> <?php echo __("E-mail Verified"); ?></span>
            <?php
        } else {
            ?>
            <button class="btn btn-warning" id="verifyEmail"><i class="fa fa-envelope"></i> <?php echo __("Verify e-mail"); ?></button>

            <script>
                $(document).ready(function () {

                    $('#verifyEmail').click(function (e) {
                        e.preventDefault();
                        modal.showPleaseWait();
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $global['webSiteRootURL'] ?>objects/userVerifyEmail.php?users_id=<?php echo $user->getBdId(); ?>"
                                        }).done(function (response) {
                                            if (response.error) {
                                                swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                                            } else {
                                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Verification Sent"); ?>", "success");
                                            }
                                            modal.hidePleaseWait();
                                        });
                                    });

                                });
            </script>
            <?php
        }
        ?>
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label"><?php echo __("New Password"); ?></label>
    <div class="col-md-8 inputGroupContainer">
        <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
            <input  id="inputPassword" placeholder="<?php echo __("New Password"); ?>" class="form-control"  type="password" value="" >
        </div>
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label"><?php echo __("Confirm New Password"); ?></label>
    <div class="col-md-8 inputGroupContainer">
        <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
            <input  id="inputPasswordConfirm" placeholder="<?php echo __("Confirm New Password"); ?>" class="form-control"  type="password" value="" >
        </div>
    </div>
</div>

<div class="form-group <?php if(!empty($advancedCustomUser->doNotShowMyChannelNameOnBasicInfo)){echo " hidden ";} ?>">
    <label class="col-md-4 control-label"><?php echo __("Channel Name"); ?></label>
    <div class="col-md-8 inputGroupContainer">
        <div class="input-group">
            <span class="input-group-addon"><i class="fab fa-youtube"></i></span>
            <input  id="channelName" placeholder="<?php echo __("Channel Name"); ?>" class="form-control"  type="text" value="<?php echo $user->getChannelName(); ?>" >
        </div>
    </div>
</div>

<div class="form-group <?php if(!empty($advancedCustomUser->doNotShowMyAnalyticsCodeOnBasicInfo)){echo " hidden ";} ?>">
    <label class="col-md-4 control-label"><?php echo __("Analytics Code"); ?></label>
    <div class="col-md-8 inputGroupContainer">
        <div class="input-group">
            <span class="input-group-addon"><i class="fas fa-chart-line"></i></span>
            <input  id="analyticsCode" placeholder="UA-123456789-1" class="form-control"  type="text" value="<?php echo $user->getAnalyticsCode(); ?>" >
        </div>
        <small><?php echo __("Track your videos with Google analytics"); ?></small>
    </div>
</div>

<div class="form-group <?php if(!empty($advancedCustomUser->doNotShowMyAboutOnBasicInfo)){echo " hidden ";} ?> ">
    <label class="col-md-4 control-label"><?php echo __("About"); ?></label>
    <div class="col-md-8 inputGroupContainer">
        <textarea id="textAbout" placeholder="<?php echo __("About"); ?>" class="form-control"  ><?php echo $user->getAbout(); ?></textarea>
    </div>
</div>

<div class="form-group">
    <div class="col-md-4">
        <img id="inputNextVideo-poster" src="view/img/notfound.jpg" class="ui-state-default" alt="">
    </div>
    <div class="col-md-8 inputGroupContainer">
        <label class="control-label"><?php echo __("Featured Video"); ?></label>
        <input id="inputFeaturedVideo" placeholder="<?php echo __("Featured Video"); ?>" class="form-control first">
        <input id="inputFeaturedVideoClean" placeholder="<?php echo __("Featured Video URL"); ?>" class="form-control last" readonly="readonly">
        <input type="hidden" id="inputFeaturedVideo-id">
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label"><?php echo __("Privacy"); ?></label>
    <div class="col-md-8 inputGroupContainer">
        <select class="form-control" id="inputPrivate">
            <option value="0"><?php echo __("Public"); ?></option>
            <option value="1"><?php echo __("Private"); ?></option>
        </select>
    </div>
</div>

<?php
YouPHPTubePlugin::getMyAccount(User::getId());
?>

<div class="form-group">
    <div class="col-md-12 ">
        <div id="croppie"></div>
        <center>
            <a id="upload-btn" class="btn btn-primary"><i class="fa fa-upload"></i> <?php echo __("Upload a Photo"); ?></a>
        </center>
    </div>
    <input type="file" id="upload" value="Choose a file" accept="image/*" style="display: none;" />
</div>

<div class="form-group">
    <div class="col-md-12 ">
        <div id="croppieBg"></div>
        <center>
            <a id="upload-btnBg" class="btn btn-success"><i class="fa fa-upload"></i> <?php echo __("Upload a Background"); ?></a>
        </center>
    </div>
    <input type="file" id="uploadBg" value="Choose a file" accept="image/*" style="display: none;" />
</div>
<script>
    var uploadCrop;

    function isAnalytics() {
        str = $('#analyticsCode').val();
        return str === '' || (/^ua-\d{4,9}-\d{1,4}$/i).test(str.toString());
    }

    function readFile(input, crop) {
        console.log(input);
        console.log($(input)[0]);
        console.log($(input)[0].files);
        if ($(input)[0].files && $(input)[0].files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                crop.croppie('bind', {
                    url: e.target.result
                }).then(function () {
                    console.log('jQuery bind complete');
                });

            }

            reader.readAsDataURL($(input)[0].files[0]);
        } else {
            swal("Sorry - you're browser doesn't support the FileReader API");
        }
    }

    function updateUserFormSubmit() {

        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>objects/userUpdate.json.php',
            data: {
                "user": $('#inputUser').val(),
                "pass": $('#inputPassword').val(),
                "email": $('#inputEmail').val(),
                "name": $('#inputName').val(),
                "about": $('#textAbout').val(),
                "channelName": $('#channelName').val(),
                "analyticsCode": $('#analyticsCode').val(),
                "featuredVideoId": $('#inputFeaturedVideo-id').val(),
                "private": $('#inputPrivate').val()
            },
            type: 'post',
            success: function (response) {
                if (response.status > "0") {
                    uploadCrop.croppie('result', {
                        type: 'canvas',
                        size: 'viewport'
                    }).then(function (resp) {
                        console.log("userSavePhoto");
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $global['webSiteRootURL']; ?>objects/userSavePhoto.php",
                            data: {
                                imgBase64: resp
                            },
                            success: function () {
                                console.log("userSaveBackground");
                                uploadCropBg.croppie('result', {
                                    type: 'canvas',
                                    size: 'viewport'
                                }).then(function (resp) {
                                    $.ajax({
                                        type: "POST",
                                        url: "<?php echo $global['webSiteRootURL']; ?>objects/userSaveBackground.php",
                                        data: {
                                            imgBase64: resp
                                        }, success: function (response) {
                                            console.log("SavePersonal");
                                            modal.hidePleaseWait();
<?php if (empty($advancedCustomUser->disablePersonalInfo)) { ?>
                                                savePersonalInfo();
<?php } ?>
                                        }
                                    });
                                });
                            }
                        });
                    });
                } else if (response.error) {
                    swal("<?php echo __("Sorry!"); ?>", response.error, "error");
                    modal.hidePleaseWait();
                } else {
                    swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user has NOT been updated!"); ?>", "error");
                    modal.hidePleaseWait();
                }
            }
        });
    }
    $(document).ready(function () {
        $('#inputPrivate').val(<?php echo $user->isPrivate() ? "1" : "0"; ?>);

        // handle featured video entry
        // TODO set what was stored
        $("#inputFeaturedVideo").autocomplete({
            minLength: 0,
            source: function (req, res) {
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/videos.json.php',
                    type: "POST",
                    data: {
                        searchPhrase: req.term
                    },
                    success: function (data) {
                        res(data.rows);
                    }
                });
            },
            focus: function (event, ui) {
                $("#inputFeaturedVideo").val(ui.item.title);
                return false;
            },
            select: function (event, ui) {
                $("#inputFeaturedVideo").val(ui.item.title);
                $("#inputFeaturedVideoClean").val('<?php echo $global['webSiteRootURL']; ?>video/' + ui.item.clean_title);
                $("#inputFeaturedVideo-id").val(ui.item.id);
                $("#inputFeaturedVideo-poster").attr("src", "videos/" + ui.item.filename + ".jpg");
                return false;
            }
        }).autocomplete("instance")._renderItem = function (ul, item) {
            return $("<li>").append("<div>" + item.title + "<br><?php echo __("Uploaded By"); ?>: " + item.user + "</div>").appendTo(ul);
        };

        $('#upload').on('change', function () {
            readFile(this, uploadCrop);
        });
        $('#upload-btn').on('click', function (ev) {
            $('#upload').trigger("click");
        });
        $('#uploadBg').on('change', function () {
            readFile(this, uploadCropBg);
        });
        $('#upload-btnBg').on('click', function (ev) {
            $('#uploadBg').trigger("click");
        });
<?php
if (!empty($advancedCustomUser->forceLoginToBeTheEmail)) {
    ?>
            $('#inputUser').on('keyup', function () {
                $('#inputEmail').val($(this).val());
            });
    <?php
}
?>


        uploadCrop = $('#croppie').croppie({
            url: '<?php echo $user->getPhoto(); ?>',
            enableExif: true,
            enforceBoundary: false,
            mouseWheelZoom: false,
            viewport: {
                width: 150,
                height: 150
            },
            boundary: {
                width: 150,
                height: 150
            }
        });

        uploadCropBg = $('#croppieBg').croppie({
            url: '<?php echo $user->getBackgroundURL(); ?>',
            enableExif: true,
            enforceBoundary: false,
            mouseWheelZoom: false,
            viewport: {
                width: 1250,
                height: 250
            },
            boundary: {
                width: 1250,
                height: 250
            }
        });
        $('#updateUserForm').submit(function (evt) {
            evt.preventDefault();
            if (!isAnalytics()) {
                swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your analytics code is wrong"); ?>", "error");
                $('#inputAnalyticsCode').focus();
                return false;
            }
            $('#aBasicInfo').tab('show');
            modal.showPleaseWait();
            var pass1 = $('#inputPassword').val();
            var pass2 = $('#inputPasswordConfirm').val();
            // password dont match
            if (pass1 != '' && pass1 != pass2) {
                modal.hidePleaseWait();
                swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your password does not match!"); ?>", "error");
                return false;
            } else {
                setTimeout(function () {
                    updateUserFormSubmit();
                }, 1000);
                return false;
            }
        });
    });
</script>