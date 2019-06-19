                <!--
                var AllowedChars="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_01234567890"
                function GlobalCheck()
                {
                var noerror=CheckPassword()
                if(noerror) noerror=CheckPassword2()
                if(noerror) {
                document.regform.submit();
                }
                }

                function CheckPassword()
                {
                var PassMinLength=6
                var PassMaxLength=20

                var pass=document.regform.password.value
                //document.regform.password.value=pass.toLowerCase()
                // to be OK for FTPMax

                if(pass == "")
                {
                alert("Aucun mot de passe?");
                document.regform.password.focus()
                return false
                }
                if(pass.length < PassMinLength)
                {
                alert("le mot de passe doit avoir au moins " + PassMinLength + " caractères!")
                document.regform.password.focus()
                return false
                }
                if(pass.length > PassMaxLength)
                {
                alert("Mot de passe trop long!\nau maximum " + PassMaxLength + " caractères.")
                document.regform.password.focus()
                return false
                }

                for (i=0; i < pass.length;i++)
                {
                 if(AllowedChars.indexOf(pass.charAt(i)) == -1)
                        {
                        var Symb
                        if(pass.charAt(i) == " ") Symb="Space"
                         else Symb=pass.charAt(i)
                        alert ("Le mot de passe ne peux contenir le caractère \"" + Symb + "\". Retapez un nouveau mot de passe.")
                        document.regform.password.focus()
                        return false
                        }
                }

                return true
                }

                function CheckPassword2()
                {
                if(document.regform.password2.value !== document.regform.password.value)
                {
                alert("Les mots de passe ne correspondent pas ! Retapez un nouveau mot de passe.")
                document.regform.password2.select()
                document.regform.password2.focus()
                return false
                }
                return true
                }
                //-->