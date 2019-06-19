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
                alert("Nessuna password?");
                document.regform.password.focus()
                return false
                }
                if(pass.length < PassMinLength)
                {
                alert("La password deve avere almeno " + PassMinLength + " caratteri!")
                document.regform.password.focus()
                return false
                }
                if(pass.length > PassMaxLength)
                {
                alert("Password troppo lunga!\nUsare al massimo " + PassMaxLength + " caratteri.")
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
                        alert ("La password non può contenere il carattere \"" + Symb + "\". Reinserire la password.")
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
                alert("Le password non corrispondono! Reinserire le password.")
                document.regform.password2.select()
                document.regform.password2.focus()
                return false
                }
                return true
                }
                //-->