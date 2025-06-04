$(document).ready(function () {
    $(document).on("click", "#users-tab", function (e) {
        $("#email").prop("disabled", false);
        $("#user-form").trigger("reset");
        $("#id").val("");
    });

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    var user_id;

    $.ajax({
        type: "POST",
        url: "/user-view-data",
        success: function (response) {
            var table = "";
            $(".user-table tbody").empty();
            $.each(response, function (indexInArray, valueOfElement) {
                table +=
                    "<tr>" +
                    '<td class="text-center">' +
                    ++indexInArray +
                    "</td>" +
                    '<td class="text-center">' +
                    valueOfElement.name +
                    "</td>" +
                    '<td class="text-center">' +
                    valueOfElement.email +
                    "</td>" +
                    '<td class="text-center">' +
                    valueOfElement.role.name +
                    "</td>";

                table +=
                    '<td class="text-center"><span style="color:darkblue;" data-id="' +
                    valueOfElement.id +
                    '"class="icon ri-edit-2-fill user-edit"></span> &nbsp; &nbsp;<span style="color:red;" data-id="' +
                    valueOfElement.id +
                    '" class="icon ri-chat-delete-fill user-delete"></span> </td>';
                ("</tr>");
            });
            $(".user-table tbody").append(table);
            $(".user-table").DataTable({
                destroy: true,
                processing: true,
                select: true,
                paging: true,
                lengthChange: true,
                searching: true,
                info: false,
                responsive: true,
                autoWidth: false,
            });
        },
    });

    $("#password").on("blur", function () {
        var password = $("#password").val();
        var errorSpan = $("#passwordLen-error");
        if (password.length < 8) {
            errorSpan.text("* Passwords must be atleast 8 characters long");
            errorSpan.css({
                color: "red", // Add CSS color property
                fontSize: "12px", // Add CSS font-size property
            });
        } else {
            errorSpan.text("");
        }
    });

    $("#confirm-password").on("keyup", function () {
        var password = $("#password").val();
        var confirmPassword = $(this).val();
        var errorSpan = $("#password-error");

        var errorMessage = "";
        for (var i = 0; i < confirmPassword.length; i++) {
            if (password[i] !== confirmPassword[i]) {
                errorMessage = "* Passwords do not match";
                break;
            }
        }
        errorSpan.text(errorMessage);
        errorSpan.css({
            color: errorMessage ? "red" : "inherit",
            fontSize: "12px",
        });
    });

    $("#confirm-password").on("blur", function () {
        var password = $("#password").val();
        var confirmPassword = $(this).val();
        var errorSpan = $("#password-error");

        var errorMessage = "";

        if (password.length !== confirmPassword.length) {
            errorMessage = "* Passwords do not match";
        } else {
            for (var i = 0; i < confirmPassword.length; i++) {
                if (password[i] !== confirmPassword[i]) {
                    errorMessage = "* Passwords do not match";
                    break;
                }
            }
        }

        errorSpan.text(errorMessage);
        errorSpan.css({
            color: errorMessage ? "red" : "inherit",
            fontSize: "12px",
        });
    });

    $("#user-form").on("submit", function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            type: "POST",
            url: "/user-store-data",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                console.log(data);
                if (data["flag"] == "Y") {
                    //Succcess for creation of new user
                    $(".table_msg1").show();
                    $(".table_msg1").delay(2000).fadeOut();
                    $("#table-form").trigger("reset");
                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);
                } else if (data["flag"] == "YY") {
                    //Succcess for editing an user
                    $(".table_msg5").show();
                    $(".table_msg5").delay(2000).fadeOut();
                    $("#table-form").trigger("reset");
                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);
                } else if (data["flag"] == "N") {
                    //Error while creation of new user
                    $(".table_msg2").show();
                    $(".table_msg2").delay(2000).fadeOut();
                    $("#table-form").trigger("reset");
                } else if (data["flag"] == "VE") {
                    //Validation Errors
                    $(".table_msg2").show();
                    $(".table_msg2").delay(2000).fadeOut();
                    $("#table-form").trigger("reset");
                } else if (data["flag"] == "NN") {
                    //Error while editing an user
                    $(".table_msg6").show();
                    $(".table_msg6").delay(2000).fadeOut();
                    $("#table-form").trigger("reset");
                }
            },
            error: function (xhr, status, error) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function (field, messages) {
                        var fieldElement = $('[name="' + field + '"]');
                        var errorMessage = $(
                            '<span class="error">' + messages[0] + "</span>"
                        );
                        fieldElement.after(errorMessage);
                        errorMessage.css({
                            color: "red",
                            fontSize: "12px",
                        });
                    });
                }
            },
        });
    });

    $(document).on("click", ".user-edit", function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        var value;
        $.ajax({
            type: "POST",
            url: "/user-show-data",
            data: { id },
            cache: false,
            success: function (data) {
                $("#user-form").trigger("reset");
                $("#id").val(data[0]["id"]);
                $("#name").val(data[0]["name"]);
                $("#email").val(data[0]["email"]);
                $("#users-tab").tab("show");
                $("#email").prop("disabled", true);
            },
        });
    });

    $(document).on("click", ".user-delete", function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        $.ajax({
            type: "POST",
            url: "/user-delete-data",
            data: { id },
            cache: false,
            success: function (data) {
                if (data["flag"] == "Y") {
                    $(".table_msg3").show();
                    $(".table_msg3").delay(5000).fadeOut();
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                } else {
                    $(".table_msg4").show();
                    $(".table_msg4").delay(1000).fadeOut();
                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);
                }
            },
        });
    });

    $(document).on("click", ".get-id-type", function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        $.ajax({
            type: "POST",
            url: "/user-get-id-type",
            data: { id },
            cache: false,
            success: function (data) {
                $(".get-document-name").html(data[0]["document_name"]);
                $(".get-user-name").html(data[0]["name"]);
                $("#userdocument_modal").modal("show");
                var url = data[0]["path_file"];
                $(".document_path").attr("src", url);
            },
        });
    });

    $(document).on("click", ".change_user_active", function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        if (confirm("Do you want to change it's status ?")) {
            $.ajax({
                type: "POST",
                url: "user-change-active",
                data: { id },
                success: function (response) {
                    if (response["flag"] == "Y") {
                        $(".table_msg7").show();
                        $(".table_msg7").delay(1000).fadeOut();
                        location.reload();
                        return false;
                    } else if (response["flag"] == "N") {
                        $(".table_msg7").show();
                        $(".table_msg7").delay(1000).fadeOut();
                        location.reload();
                        return false;
                    } else {
                        $(".table_msg4").show();
                        $(".table_msg4").delay(1000).fadeOut();
                        location.reload();
                        return false;
                    }
                },
            });
        } else {
            return false;
        }
    });

    $("#btn-send-user-notification").click(function (e) {
        e.preventDefault();
        var title = $("#title").val();
        var body = $("#body").val();
        if (title == "") {
            $(".msg1").show();
            return false;
        } else if (body == "") {
            $(".msg2").show();
            return false;
        } else {
            $.ajax({
                type: "POST",
                url: "send-user-notification",
                cache: false,
                data: { title, body, user_id },
                success: function (response) {
                    console.log(response);
                    if (response["flag"] == "VE") {
                        $(".msg6").show();
                        $(".msg6").delay(1000).fadeOut();
                        $("#title").val("");
                        $("#body").val("");
                    } else if (response["flag"] == "A") {
                        $(".msg3").show();
                        $(".msg3").delay(1000).fadeOut();
                        $("#title").val("");
                        $("#body").val("");
                    } else if (response["flag"] == "F") {
                        $(".msg4").show();
                        $(".msg4").delay(1000).fadeOut();
                        $("#title").val("");
                        $("#body").val("");
                    } else if (response["flag"] == "X") {
                        $(".msg5").show();
                        $(".msg5").delay(1000).fadeOut();
                        $("#title").val("");
                        $("#body").val("");
                    }
                },
            });
        }
    });

    $("#title").click(function (e) {
        $(".msg1").hide();
    });

    $("#body").click(function (e) {
        $(".msg2").hide();
    });
});
