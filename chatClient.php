<?php
/**
 * Created by PhpStorm.
 * User: sayho
 * Date: 2018. 1. 31.
 * Time: PM 1:46
 */
?>
<? include_once $_SERVER["DOCUMENT_ROOT"] . "/include/head.html" ?>
<? include_once $_SERVER["DOCUMENT_ROOT"] . "/common/classes/ApiChat.php" ; ?>
<? include_once $_SERVER["DOCUMENT_ROOT"] . "/pro_inc/check_login.php" ?>
<?
    $class = new ApiChat($_REQUEST);
    $senderInfo = $class->getSenderInfo();
    $receiverInfo = $class->getReceiverInfo();

    $sender = $_REQUEST["sender"];
    $receiver = $_REQUEST["receiver"];

    if($_REQUEST["isA"] != "1")
        $myIndex = $_SESSION['member_edimak_idx'];
?>
<link rel="stylesheet" href="./css/style.css">
<script src="./js/jquery-3.3.1.min.js"></script>
<script>
    $(document).ready(function(){
        var sender = "<?=$sender?>";
        var receiver = "<?=$receiver?>";
        var roomKey = "";
        var isA = "<?=$_REQUEST["isA"]?>";
        var myIndex = "<?=$myIndex?>";
        var id = "";
        var senderName = "<?=$senderInfo["member_gubun"] == "SPE" ? $senderInfo["com_name"] : $senderInfo["user_name"]?>";
        var receiverName = "<?=$receiverInfo["member_gubun"] == "SPE" ? $receiverInfo["com_name"] : $receiverInfo["user_name"]?>";

        function calculate(){
//            var headerHeight = document.getElementById("headerBox").clientHeight;
            var headerHeight = $("#headerBox").outerHeight();
//            var noticeHeight = document.getElementById("notice").clientHeight;
            var noticeHeight = $("#notice").outerHeight();
//            var footerHeight = document.getElementById("footerBox").clientHeight;
            var footerHeight = $("#footerBox").outerHeight();
//            var totalHeight = document.getElementById("live-chat").clientHeight;
            var totalHeight = $("#live-chat").outerHeight();
            var calculated = totalHeight - headerHeight - noticeHeight - footerHeight;
            $("#chat-history").css("height", calculated + "px");
            $("#chat-history").outerHeight(calculated);
            var width = document.getElementById("live-chat").clientWidth;
            $("#chat-history").outerWidth(width);
            $("#footerBox").css("width", width);
        }

        function getChatHtml(sender, content, type, time){
            var html = $(".jCopyTemplate").html()
                .replace("{#NAME#}", sender)
                .replace("{#TYPE#}", type)
                .replace("{#CONTENT#}", content)
                .replace("{#TIME#}", time);
            return html;
        }

        function getChatList(sender, receiver){
            if(isA != "1") id = myIndex;
            $.ajax({
                url : "/router.php?route=ApiChat.checkAndRetrieve",
                async : true,
                cache : false,
                dataType : "json",
                data : {
                    sender: sender,

                    receiver: receiver,
                    myIndex: id
                },
                success : function(data){
                    $(".chat-history").html("");
                    roomKey = data.entity.roomKey;
                    var list = data.entity.messageList;

//                    if("<?//=$receiverInfo["member_gubun"]?>//" == "SPE"){
                    if("<?=$receiverInfo["member_type"]?>" != "GEN"){
                        $(".chat-history").append(getChatHtml(receiverName, "방문해주셔서 감사합니다:) 어떻게 도와드릴까요?", "receiver", ""));
                    }
                    for(var i=0; i<list.length; i++){
                        if(list[i].member_fk == sender) $(".chat-history").append(getChatHtml("", list[i].message, "sender", list[i].regDate));
                        else $(".chat-history").append(getChatHtml(receiverName, list[i].message, "receiver", list[i].regDate));
                    }
                },
                complete : function(){
                    var elemScrollHeight = document.getElementById("chat-history").scrollHeight;
                    $(".chat-history").animate({scrollTop: elemScrollHeight}, 0.1);
                }
            });
        }

        $("input[name=chatBox]").keydown(function(key){
            if(key.keyCode == 13){
                var msg = $("[name=chatBox]").val();

                $("[name=chatBox]").val("");
                $(".chat-history").append(getChatHtml("", msg, "sender", ""));

                $.ajax({
                    url: "/router.php?route=ApiChat.sendMessage",
                    async: false,
                    cache: false,
                    dataType: "json",
                    data: {
                        roomKey: roomKey,
                        sender: sender,
                        receiver: receiver,
                        message: msg
                    },
                    success: function(data){
                        getChatList(sender, receiver);
                    },
                    error: function(req, res, err){
                        alert(req+res+err);
                    }
                });
            }
        });

        $(".jSend").click(function(){
            var msg = $("[name=chatBox]").val();
            $("[name=chatBox]").val("");
            $(".chat-history").append(getChatHtml("", msg, "sender", ""));
            $.ajax({
                url: "/router.php?route=ApiChat.sendMessage",
                async: false,
                cache: false,
                dataType: "json",
                data: {
                    roomKey: roomKey,
                    sender: sender,
                    receiver: receiver,
                    message: msg
                },
                success: function(data){
                    getChatList(sender, receiver);
                },
                error: function(req, res, err){
                    alert(req+res+err);
                }
            });
        });

        window.onresize = function(){
            window.resizeTo(450,620);
        };

        calculate();
        getChatList(sender, receiver);

        setInterval(function(){
            getChatList(sender, receiver);
        }, 10000);
    });
</script>

<div class="jCopyTemplate" style="display:none;">
    <div class="chat-message clearfix">
<!--        <img src="http://gravatar.com/avatar/2c0ad52fc5943b78d6abe069cc08f320?s=32" alt="" width="32" height="32">-->
        <div class="chat-message-content clearfix">
            <span class="chat-time">{#TIME#}</span>
            <h5>{#NAME#}</h5>
            <div class="balloon {#TYPE#}"><span>{#CONTENT#}</span></div>
        </div>
    </div>
    <hr>
</div>

<div id="live-chat" style="background-color: white">
    <header class="clearfix" style="text-align: center;" id="headerBox">
        <h4 style="color: white">
            <?=$receiverInfo["member_gubun"] == "SPE" ? $receiverInfo["com_name"] : $receiverInfo["user_name"]?>
        </h4>
    </header>

    <fieldset id="notice">
        SMS 알림서비스는 일반회원의 최초 문의와 전문회원의 최초 답변시에만 제공되므로, 그 이후의 문의와 답변은 마이페이지-->메시지관리에서 확인 부탁드립니다.
    </fieldset>

    <div class="chat">
        <div class="chat-history" id="chat-history" style="overflow: scroll">   </div>

        <?if($_REQUEST["isA"] != "1"){?>
        <div class="msgBox" id="footerBox">
            <fieldset>
                <input type="text" name="chatBox" placeholder="메세지를 입력해 주세요" autofocus>
                <button class="button jSend" value="전송">전송</button>
            </fieldset>
        </div>
        <?}?>
    </div>
</div>
