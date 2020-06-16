// version 2.2
class HTMLEditor {
	constructor(element, title, newwindow) {
		this.title = title;
		this.element = element;
		console.log(this.element);
		if (newwindow) {
			this.newWin = true;
			this.win = null;
		} else {
			this.newWin = false;
			this.win = window;
		}
		this.mainInit();
		this.element.parentNode.insertBefore(this.btn_editor,this.element);
		this.initOnClick();
	}
	mainInit() {
		this.mainContainer = document.createElement("div");
		this.buttonsDiv = document.createElement("div");
		this.buttonsDiv.setAttribute("class","htmleditor-btns-container");
		this.mainContainer.style.display = "none";
		this.element.parentNode.insertBefore(this.mainContainer, this.element);
		this.mainContainer.innerHTML = "<iframe style='width:99%;height:90vh;background:#fff' src=\"javascript: document.open(); document.close();\" frameborder='1'></iframe>" + this.mainContainer.innerHTML;
		this.frame = this.mainContainer.childNodes[0];
		this.frame.contentDocument.designMode = "on";
		this.frame.contentDocument.execCommand("styleWithCSS", true, true);
		this.mainContainer.insertBefore(this.buttonsDiv, this.frame);
		
		var iconWidth = "21";
		this.btn_bold = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAKYSURBVGhD7drLq01hGMfxjUgxICUiA8LA0ACllExccitCpCRJISETl4xIJgxcBigDf4JMTCWZKQrlNjAQyp3cvr/aq3a7Z79nrXc/z7sn+1efzuS8z9pPZ+213stpDTNMmUzEwkALMBtTMBphWYp/hXzGPZzDXLimZCOd/uIWZsElg2qkor/SVvSdmbiKj7AuVMoJuGQCbsK6SAm61VbCJWPxGNaFLO+xB1uwqf1zHy7iCawxKU8xCi45DusilldIZQ1ewxrbi76zLtkJ6wKWkRpR9B18AWu85Shcsg3WBSx1GlHWwRpvuQCXRDQyBh9g1eh2CS6JaER5CKtGt5NwSVQjdZ9iug1dEtHIJPyAVaOTfkeTWJdENHIE1vhuN+AW70YW4xus8Z2+Q9N9t3g1orWH3vJfYI3tdhCuadLIG0xum4r5WI8zeAlrjOUK3KYmVZo00i9NFM8iZOVYqpEHWIawlGrkPg5jEdxvK6XkrVV5jkMYB7cMopHKMyyBS5o08g4bsKJtNbT+Po3b+AVrXIreJ/oMfadJIyO9EPVIPo+fsMb38hur0Fc8G6miVV/daXzlLTRHy05EI4o2FqwaKaeQnahGlLuw6vSiJXL2ozmykf2w6qTMQ1YiG9EX2KqTshFZiWxEj2irTspeZCWykSZbTRXdjlmJbOQarDopO5CVqEamQTvvVp2U7ClLRCOaDN6BVSPlE8YjK7tgFbXUaUQHOU3fH5XryI52+qyils6lbmU6dG6oyaOOKepsA1m0YTEHWam741HCbjTODGjq/RVW0ZI089Uiq3H0VLAKDoIOmZYjK4M+DP0DLcLWQrv22SndiFaNj3AZ26Hb2iU6UToW5AB0xrgZWgbrnwR0TjnMMMOEp9X6D49WTMj3QAJpAAAAAElFTkSuQmCC\">","htmleditor-bold","Жирный");
		this.btn_italic = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAFeSURBVGhD7dg7KIZRAMbxTzJQyGSQTKJsSjG77FiYkDK5bVJW15mUSZgMipWyk0XKpYzKSLK5/U9Rb09vH7bzcP71W753OU99w3veQiqVSv37GjGKMTGV89sAyhFdtXjA+y/sIrpakXfYYi4QXSVYxDVucQc9+DPCs+AE3Yi+PuiQbdg1AR2yDLvC30yHTMKuLeiQfth1BB3SDruuoEPqYdcTsiNeUQarqpAdEdzDrmbokFPY1QUdsg+7hqBD1mHXLHTIHOxahQ4ZgV170CE9sCu8ouuQFtiVdxepgVWleEF2RLhQ2VWH7IjgBna1QYccw65e6JAd2DUOHbICuxagQ6Zh1yZ0yJ+54nbArkvokAbY9YjsiDfYXXErkR0RWF5xm6BDzmBXJ3TIASwaxNKncC/XIef4ej6M8NU+usKtTw/+nSi//84g77DFzCO6KrCGwx/aQDVSqVQq5V6h8AEtEuL1xvFe8AAAAABJRU5ErkJggg==\">","htmleditor-italic","Курсив");
		this.btn_underline = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAFzSURBVGhD7di/K0VhGMDxk/wsMmCTWJXJINlJRtHdKOUfsPgLUAYjMhhkU/gDWJRMBiyKYiNZyI+B+D7DXd6eczrec99zdHq+9dnufd/3uXXOufdGlmVZWkOYUjTAt1G4600gaLv4UbTDtwu4690jaDZIQjZIlmyQhGyQLNkgCdkgWbJBErJBsmSDJGSDZKn0g3TAt0u46wUfZBvupqIHvsmh3fVuELRVuJuKfvj2CHe9MwRtAe6mYhI+dUFb7wBBG4e28RZ8moG23jKC1oRXuBt/ohd/qRFXcNcSwwjeDrTN5TbaiTTJ/2Bxd8A71CF4cod6g3aIB8yiDVr1GMM5tPcL+YMut+Iu+qp3nGAPm5BP/whP0F5ftY/cW4J2GF8yaDMKaR4v0A6W1jfW0IJC68MGtLtZki8cYgT/qlZMQ57+x7iGPLU/8IxbnGIdc+iGZZUtubAHczCAoMV9rai1wn4h1poNkrYKVnKwCMuyrNIWRb9cPtoss8eZ4AAAAABJRU5ErkJggg==\">","htmleditor-underline","Подчернутый");
		this.btn_strike = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAFESURBVGhD7di/K0VxGMfxY2KR2WBTFmUwWIw2s4hdJmUgs8loMliU8h+QH6uUxWJgUUImJQNl8eP9XJ36dvo6zjl1vj30eder3Nsz3M8ZbjeZUkrFGsNUApNotV18JnCLVosNecQGljGPOdhTPUd494r8idvdItZwifDOJB9yhwHE2kN4+4xYPThAeJt0iD3hEfxU1SFWH66Q3yYdsmRvlFRniDWKd9htsiE36LY3Sqo7xNpB0iELnVflNRkyhA+0PmQLD+jtvCqvyRDrEBfff/qo6RB3aYi3NMRbGuIt10OGMVHRGcIhL4jdxYyj1Yo/49uS9NdvmzSkajNYr+ga4Yd7Q+wuZhVu0tevtzTEW/9myCnCIaYff6YuzCL/107oCINw2zS2cYwnFAcU3WMfK3DVJmIf+DcnUEoppZRSStUoy74A08tCbgudZxUAAAAASUVORK5CYII=\">","htmleditor-strike","Зачеркнуть");
		this.btn_left = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAACfSURBVGhD7dqxCcMwEIXhWyFrpMoALj1FFskKmSXDeYVYpYpXCGyOe9L/w9dcIXDhA2EHFeyYRPwnIYeO5NBR7JMgolX6FPBsLqd2crZ3czl1cDYepO9XwNYQEY31mITc647k0JEcOpJDR/LFcUREq6QuOtluuVipnZyNO3ufOjjbLQ/yLeDVEBGNpT4sOpJ73ZEcOpJDR/IHFUdUqIgTR8eFQSBdekUAAAAASUVORK5CYII=\">","htmleditor-left","Выравнивание по левому краю");
		this.btn_center = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAACmSURBVGhD7dexCQJBEIXhRcxEzAwNLjbSFmzBxEq0B+EywQKswhZswRZsQV967oRz7oz8P3zZJi8Z2EI0fu+ELlJlPYzuv4e8EjoLEdGwiXQNLcWlhVg3/Fdu4hJDnLgNmcuzoV6IiOq2Ca2kyrrh0fFnj8Yc8kjoKEREw6aya2gtLvHVdcKQ72Zyb+gkRER1+4Q2UmXd8Oj4s0djDrkmdBCi8SrlAzhjF7SJIG3uAAAAAElFTkSuQmCC\">","htmleditor-center","Выравнивание по центру");
		this.btn_right = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAACPSURBVGhD7doxDYBAEETRtYANKgRQogIjWEAL4rAAJ2AKkiNkZ/kv+c10V3AJgUBCZ5HiKpIcHZOjY7EUCQCeGVtbgrqtLXWvf103DvJy3ebWkSAAfzEUSd7JjsnRMTk6JkfH5IPjGAA8U+bFinf2l+tW5iBTa08QgL9QHxYdk3eyY3J0TI6OyR9UHEMiETdH04VBbCYaDQAAAABJRU5ErkJggg==\">","htmleditor-right", "Выравнивание по правому краю");
		this.btn_link = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAPMSURBVGhDzZpJyE5RGMc/48KQeVjYkCmslHkoFDJkSCQbYmfYUEQISYokRSyUpWFPsVCGyEwWSBkyLcyRofD7p1tPp3Pu9w7Pfe/3r1/f1/ue9zzn/95zz3mec9+mZjQR9sJ1eAW/4C3chcMwC9pCi9VUuAZ/K+AxLIVW0GLUGvbAH4gNOo+T0BFKVxs4A7FBVspt6AWlSlciNjjdDxtgAgyGcbAaLkOs/X0ozcwUCKfTd1gOeXN/HnwA+zlRmpnwxv4G+uYr0SB4AfbzouFmNGXCQayAajQASjcT3ht3oJaltHQz2uxs4PVQq0o1ox3bBh0P9ag0Mz/BBhwC9UpmnoPtVxRq5h3YYKPBQw03ow3PBloDXkqZuQddwFXKYm2Qq+CplJmzoLTITbMhDDIfPJUysw3cpHpCqbgNoLRjIHhKZl6DjfMZeoKbVE/YAEJLqIJ7agz8BhtnB7hJO/kpsAFErWb6wxZYDGGWcBRsDK1iruoAqidsEFGtmUWgzDn7/GawGgm2f9EHXKX1Xd9QGKhSMwshLAfCVVBXKEz9R4G7Uma06uSZGQFK/8PP6fAi1EOwbeZAJi3Jvf//W79qMROrGA9B7JTlKdh2OuyQlB49A712AVw2zWrM6FgobLcfYtLgwpUry++Og31dmXnhZuzlD1e8B5DatZeBbfsJ2oF0DOx74gZ0hbqVMrMdJA1Cg7HvadmNSebC+0NfQqZ+ECsBbkI3qFsxMxtBGgb2dZ1GdoKYDoBtK2aClaZtzMwt6A51S2bOgQaqv51BmgY24BMI1R5049t24iLElDKjfa4HFKK5YINp6oQ6AbaN+ArDIaWUGZ0puOZnmVSI2UDvIUxLPoJto01zATSnlBnVUJohruoLYSClIVbnIXtPV6ISE5nyppnSKleFC4ESQyvN631wBGo5E0iZ0YpXy9FVUjvBBtCGp5TdUykzS8BNuvm+gA2gIkrBPaViL0w4VRS6PmzaCjaAaC7RrEUqw8M408FN2rV1oBAGKcKMSgMb4yC4SgmejnpsEOFtZh3Y/rXruysv0fQyo2Nd27ce0Baios0MBdvvDyhMKTNaQus1Mwlsny+hUBVlZhPY/q5A4fI2o0foYX+7oCHyNLMKwn7qfaZTlfLM6IFqJdKAw5OaS9Bwpcwo9c87ONd0Wgn24E+oHJgMpShlRmjHXgv65rXEanVSSZ1q37B7IyWZiR3PVsNp0JUqXfoBjn6IExtkHppOuhItwkQmFUWqJx5BbNAhurE13VqsVE/MAJ20aMq9AaUd2rG12e2GsZBQU9M/q8hrE0BNBf8AAAAASUVORK5CYII=\">","htmleditor-link","Вставить ссылку");
		this.btn_image = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAG7SURBVGhD7dktSARBGMbx9ROTFoNgsBgVk0EwmCwWi2gwKgiiQW0GqwbRblUsdi0aBUEM2owKFkE0KYgf/1dcGN6b3dtd17uZYx74hZubY+e5XWYXNgoJCamaywYRfTUI66CPrIM+qhhYxZTjZI163RUDg3A9ska97oqBUKSGCUVci7NFOjCBeYyhGWlxssgoHmCu4Qb9SIpzRfrwDL0GcQs5U7Y4V2QL+vimWdjiXJET6OObtmGLc0UOoY9vWoctpRfpxgKafj7lzzT08WMfGIAtpRYZR7zbHKAdeSN/wBH0GsQGklJKEdlJdvAJ83en6ELetGARF7jHGSaRlj8XkVN9Df2bmHzXi/9O4SJyCSzhFXq+doeka7usFCrSg2PoeWmeII8aWdIG2TTypFCRfeg5WbxBdqWkdGINcgbl7r6MVmRJoSKyI+k5WcmGsAIz8lgiN7oX6PlXGEG11LxIbBfDkBvg++9YErl/7CHtcqtbkSIeMQfbI71XRWLnGIIZL4sIWYOZUETPqbVQxEwoUqJSisxgs85kDWYKFXExoYhryVSkYV70+Mo66CProI+sL999FBISkpgo+gZqCGtJKgW7XgAAAABJRU5ErkJggg==\">","htmleditor-image","Вставить изображение");
		this.btn_orList = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAHkSURBVGhD7do9SBxRGIXhNWqE4E8KRYwiYiFqEQJiYTASJJU2QtBGRKuEgIUIYmMhpokgNmKbUsSAIErQ2AmBVBZqn9afRhQbE5O8Z9cPhl3c0RCYe8098MA6zsIcZoY732jqvucJGvEg/ZOHacEn/MJvDMG7dOMnVMC8g3epgQ7ezoa3RQowgRHctUgf+hNUiZz04q5FTmHfSUInchKKJOifFRnDZILqkJO/KeJk7k2RJqxc69CGkJCQEKejYao089HPvMZX/IDWkGO8RxG8idYLWwSzzcCbPMQRDjCFWdiZOYdXs3s1opeRLjM7K4+1IU9e4lWCbjw+TVxnUInv2hATJx/jdZmtw3Z6g7g4V6QQy7Ad1qBZPi5OFdEN/RH2yx08wm0yjQ8JakA6JVhFtOU3bOMLnsGL1CNaIpuGLS9Sjs/QGci2hVaEhISEuJkqNENri5fR4/shbO24whLK4FV2EV0EzQK8ylvo7eIARmFFNGx5Fw1WukcGYUX0rBUXPYu1JSjnZckcrIBouNKOcXFuHskuoj+MjiMuzk6Iz2EHp5cQtcgXp4q0ozjzMT0V7sF27EK+ODNY6Wa5xAk2sQ8rcYEKeJEXsAOP0n9CDMObaF7vwSI2oCFrHk8REvL/JpX6A/Of9EeTWhH+AAAAAElFTkSuQmCC\">","htmleditor-orlist","Нумерованный список");
		this.btn_list = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAACLSURBVGhD7dixCYAwEEbhTGLjLuIA2riBmzmMlQvpn+6KQJTAIZf3wetS5CrjJQCfHeo0zapkUfacZ/mOVZe6TZsq2ZU951m+YxWDOPZqkEmtpkGVjMqe8yzfEQDwT2G+IzxRHOtrkDD/IwAAtGBB51hfb60wg7CgAwC0YEHnWF9PlDCDsKADwknpAfpKR08qztpHAAAAAElFTkSuQmCC\">","htmleditor-list","Список");
		this.btn_font = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAIcSURBVGhD7do/SFVhGMfx2x8KoqAoKIeWkMiGJpegpSgkxByactQhhCbXWlxcIlyiKWgKoqElgqY2CaIpAoeIIJAoxCBUzNL6vsMDD/I7nvfY+957iPcHn8HLOe/zPOrlOVztZMglvMYv/HHC1+8xgdZnCL/hB1DuoLU5hi9QjW8Vhr2AVuYZVNNVPuEQWpVxqGbrPEJrcgo/oBqNcR09zx7MQTUYaxF96GluQzXnfRevbfUSu9CTDGIdqjEThujHG/dalVvoeg5gHqohbwwhZ7ACdY1ZxVl0NfehmvGewGcS6jrvLfahK7mKTahGzGccgU94D7yAut6bQfYcRd32DkOG5y2VE/gGdZ/pytaP2d73sF1Goe7zsm79mO39DvtRl4dQ93tZtn7M9l7DOcTkID5AneMl3fqx23sKTXIedY/8Sbd+zPZ+hd1ommmo87wkWz9mey/hJHaSvci+9WO39w38S05jGepsE7b+AHaUWahDvcdIkZtQ53vhJ9f41/c4fkIdaML2PowUCe+B51B1vBE0yhWog8wGLiJlwjfvK1Q90/jxZRjqIHMXOXINqp55gEapGyTns5CqZ8ogVcogEVH1TBmkyn8zSHhIfJqJqmeSD9IrZZC2KYO0TRmkygI+ZqLqmeSDlM0eEVXPlEGqlEEiouqZMkiVMkhEVD1TBqlSBomIqmcaDxL+ZenyNlJ95qui6pnw93qRTucveE+x93ePex0AAAAASUVORK5CYII=\">","htmleditor-font","Выбрать шрифт");
		this.btn_fontSize = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAE3SURBVGhD7ZqtbgJBFEZXofoKeGQfoAmKvgEGW4WtqsRRwQvUoXmD1vIGNXgMSRVJFQq+WzLJZDIzy53dKbuT7yQn4WeWyRHXzFARQorgBQ6uL/vLCJ7g29+7HvMFz/AXDuWDPjKFEmHcwFrWUBbewyfo8gD30A4RJzDKEboP/Zcz6LKEvrU7GB38LoWYAfetFaOD36UQM+Aho4O/gO8KP6Fvkx/oWx/zERrcAQ8ps9UKc+jb4BumEhrwkLWDfws5Qsbww3IL7d8+QPv7V9iYHCEu7h4S1joMUcAQDQxRwBANDFHAEA0MUcAQDQxRwBANDFHAEA0MiVDM4UMxx0FCEQd0hkZHplpyhjQ6xNaSM0RIvlbQ8gztyxrjCrZB8kVPF0m6eusqRVyGCsVcTwtF/GGAkPtRVRegnRChN2mY3QAAAABJRU5ErkJggg==\">","htmleditor-font","Размер шрифта");
		this.btn_fontColor = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAACVSURBVGhD7ZqxDYMwFAW9R6r0pGKF7JDZGIA5k/87F0YBYcEJ7qTrvqV3vcsK5vB7srlhN4Z01JCay4Q8w3GDn7A1pjZvWm+XzA2H8wpb42vzBo8hNAyhYQgNQ2gYQsMQGobQMISGITQMoWEIDUNoGELDEBqG0DCExiOc/pg3IldhCN8nmxt244eBjhpSY0hH7xJSyg9HtZqfb69k1wAAAABJRU5ErkJggg==\">","htmleditor-fontColor","Выбрать цвет шрифта");
		this.btn_fontBackground = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAALBSURBVGhD7dlLqE1RHMfx45VXEt2SlFLyioGUyMCAMFJKJkoZYcJIKaIMMVNKuRkoSomJkFcy9ChJmCjlmSKElNf3d91Vq39r77PP3mud9qr7q0/3nnPPWXv9zt3v0xlJs8zHov+/5huVeIP3yLaMK/F3WJZlbAnnArJJUYk7mIwsUlTiFiYhixSVuI7sS1xD9iWuYiKySFGJK5iALFJU4gbGI4sUlZBXmIfWp6yE0/oyRSV+Bp5rbZmiEg8wG3e955zWlRmFZ7ATvY9pUKag9WXWwk5QtsBPqjKjh382ziXYyck3rIOf2GVW4QnmDj1qEK3/v2En5qQsoxJfoPe+xgLUzhHYCVkpyvglnLeo9Z/RudIH+IOdwqB5TmKWUYnPsO/TqU+ts4btsIMthTa+VGWil1Aewh/sNlxSlElSYiXsgJvhJ2aZJCWUc/AHfImxsIlRRnukohKNLgdmwp4/7UNRYpSxGpdQDsIf9DsGUJaYZaKU0H5aN9P8gbXLrZJeymyAfZ1EKaEjp9ZXO7h2uVVTpUzZht24xGK8gx38JnpNWZm9sEdsiVJCn7g9gstTzEKdFJUJiVJiOT7CDv4IM9AkVcpEKaF19ivs4PcwHU2iG3M78Bh2fCdKCeUQQgvYibqZg6MI/Zd90Uq4HIZdyC9sRdXoUngNdAFWdu0iP3AcUUsounD6BLvAKmX0NcEu6ArOvt/SKY7ODrodWGtFJV4gtGApK7MRoQ/A9we687gJY5Ak3Uo4oTK66RC6l+VoB3ICyb9qq1rC8ctsgx6HXvccuzEVydNrCUeTPw2tLvZv+lptPbTR9yV1S5Q5i3Hoa/SJhiZT10kk24i7ZTX0vV5oYr04hr6tSmVZgcsITdKyF0H70bosw0WENmI5AEU36vSaPUOPWpwlOA//FMOVcNEFUTZZiDNo5eozkval0/kHbCBxCzJdc1AAAAAASUVORK5CYII=\">","htmleditor-fontBackground","Выбрать цвет фона");
		this.btn_save = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAMaSURBVGhD7do5qNRQGMXxURFBtLBwe4IgYiPa22hnZ2ullWBpoZULFqLYiiIWCmJjYy1urcsTURDUUhsLV1wKt8Ll/OUFQjxJvptkJkHmwA/ekvflzkzuvbk3bzTNNNNMNEvkljyscVM4drDZJr+Dtspgs1tco51dMtgcEddo55AMNufFNdo5J4MNndg12rkqE89eOSHr/37ns13eimu081xWSVnWyEnprC9tkV/CyX/Kddkh84Xw+wdSbGjEd7koKyXLJrkkP4Rjvsk6aZV5cl+KDcALuSy8OPf7FO/lgHC5ZW9a3hVplZThdNwazzuL5aW4on14JNnlnJRj4gr2aY8kZa18EVcsir7zVK7N4Wt3/ad4JUslHDqxKxTxUQ7KailmRpjROcb9bQRDcigbpek7d1vyQ2lZOOaOuBp1GI6XS204qGzIrULDFkk0HHtPXK0qr2WFhLJQTosr5HyQcPFc+GQ+iavp0Ncin/g/2SmfxRXNo080DX3G1czjctonTNCNs0EeizsBGJ3oxE3D31b1ySeyWTpJ1bvGidqGodnVBjerneWMuJPghrQNNVxtsMbpLBfEnQTjfiG8iZ2Fj9edBF1cWs/E1UZnS2I6Go11JwEddZydnYGGAadxGOoY8hj63Any2rxrkQ0LpgCmguQw6TD5uKIOk1rTCTEyT2VOCZN1KKyVuQ1wharcldRblFlxtapwP7dAasOGQORycngxVRsKWbgz5lhXow79aZmEwq2yKxLBLfphcQMAPzsqbW7jWWKEw+KFRYwrFMU7x7DKrgv4ump0imCxx6IvKSwrXbE+sfxODgt9FvyuYB/YCGFDpFHYgnFF+8DWVKuwOVYsyrXOZhqbau/mftbGG9kvZRt0rFhbrUUI25XZcMw2JtuZbGtmYVLjppLtz2ID6nyVs5Jfgxe3THlhbMt2Ej5WhmQmy7Iwf7AxXWxsGY6tWrZyruPCBvrEw6XhGu308lghGh7euEY7g37QE9lIyHS2zhhHeCjjGu0M+mFoyrwz6MfT/BMA62/3TwJ53HsN+h8Gppnm/8po9AckRYmDrypBaAAAAABJRU5ErkJggg==\">","htmleditor-save", "Вернуться к html");
		this.btn_editor = this.createButton("<img width=\""+iconWidth+"\" height=\""+iconWidth+"\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAMaSURBVGhD7do5qNRQGMXxURFBtLBwe4IgYiPa22hnZ2ullWBpoZULFqLYiiIWCmJjYy1urcsTURDUUhsLV1wKt8Ll/OUFQjxJvptkJkHmwA/ekvflzkzuvbk3bzTNNNNMNEvkljyscVM4drDZJr+Dtspgs1tco51dMtgcEddo55AMNufFNdo5J4MNndg12rkqE89eOSHr/37ns13eimu081xWSVnWyEnprC9tkV/CyX/Kddkh84Xw+wdSbGjEd7koKyXLJrkkP4Rjvsk6aZV5cl+KDcALuSy8OPf7FO/lgHC5ZW9a3hVplZThdNwazzuL5aW4on14JNnlnJRj4gr2aY8kZa18EVcsir7zVK7N4Wt3/ad4JUslHDqxKxTxUQ7KailmRpjROcb9bQRDcigbpek7d1vyQ2lZOOaOuBp1GI6XS204qGzIrULDFkk0HHtPXK0qr2WFhLJQTosr5HyQcPFc+GQ+iavp0Ncin/g/2SmfxRXNo080DX3G1czjctonTNCNs0EeizsBGJ3oxE3D31b1ySeyWTpJ1bvGidqGodnVBjerneWMuJPghrQNNVxtsMbpLBfEnQTjfiG8iZ2Fj9edBF1cWs/E1UZnS2I6Go11JwEddZydnYGGAadxGOoY8hj63Any2rxrkQ0LpgCmguQw6TD5uKIOk1rTCTEyT2VOCZN1KKyVuQ1wharcldRblFlxtapwP7dAasOGQORycngxVRsKWbgz5lhXow79aZmEwq2yKxLBLfphcQMAPzsqbW7jWWKEw+KFRYwrFMU7x7DKrgv4ump0imCxx6IvKSwrXbE+sfxODgt9FvyuYB/YCGFDpFHYgnFF+8DWVKuwOVYsyrXOZhqbau/mftbGG9kvZRt0rFhbrUUI25XZcMw2JtuZbGtmYVLjppLtz2ID6nyVs5Jfgxe3THlhbMt2Ej5WhmQmy7Iwf7AxXWxsGY6tWrZyruPCBvrEw6XhGu308lghGh7euEY7g37QE9lIyHS2zhhHeCjjGu0M+mFoyrwz6MfT/BMA62/3TwJ53HsN+h8Gppnm/8po9AckRYmDrypBaAAAAABJRU5ErkJggg==\">","htmleditor-editor", "Открыть редактор");
		var obj = this;
		this.btn_editor.onclick = function() {
			if (obj.newWin) {
				if (obj.win==null || obj.win.closed) {
					obj.open();
				} else {
					obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML = obj.getElementValue();
				}
			} else {
				obj.element.style.display = "none";
				this.style.display = "none";
				obj.mainContainer.style.display = "block";
				obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML = obj.getElementValue();
			}
		}
		this.element.onkeyup = function(){
			obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML = obj.getElementValue();
		};
	}
	
	initOnClick() {
		var obj = this;
		this.btn_strike.onclick = function() {
			obj.frame.contentDocument.execCommand("Strikethrough", false, '');
			obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
		}
		this.btn_bold.onclick = function() {
			obj.frame.contentDocument.execCommand("bold", false, '');
			obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
		}
		this.btn_italic.onclick = function() {
			obj.frame.contentDocument.execCommand("italic", false, '');
			obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
		}
		this.btn_underline.onclick = function() {
			obj.frame.contentDocument.execCommand("underline", false, '');
			obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
		}
		this.btn_left.onclick = function() {
			obj.frame.contentDocument.execCommand("justifyLeft", false, '');
			obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
		}
		this.btn_center.onclick = function() {
			obj.frame.contentDocument.execCommand("justifyCenter", false, '');
			obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
		}
		this.btn_right.onclick = function() {
			obj.frame.contentDocument.execCommand("justifyRight", false, '');
			obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
		}
		this.btn_link.onclick = function() {
			obj.frame.contentDocument.execCommand("createLink", false, obj.win.prompt("Введите ссылку, которую хотите вставить", ""));
			obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
		}
		this.btn_image.onclick = function() {
			obj.frame.contentDocument.execCommand("insertImage", false, obj.win.prompt("Введите ссылку, на картинку", ""));
			obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
		}
		this.btn_orList.onclick = function() {
			obj.frame.contentDocument.execCommand("insertOrderedList", false, "");
			obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
		}
		this.btn_list.onclick = function() {
			obj.frame.contentDocument.execCommand("insertUnorderedList", false, "");
			obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
		}
		this.btn_font.onclick = function() {
			obj.frame.contentDocument.execCommand("fontName", false, obj.win.prompt("Введите название шрифта", ""));
			obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
		}
		this.btn_fontSize.onclick = function() {
			obj.frame.contentDocument.execCommand("fontSize", false, obj.win.prompt("Введите размер", ""));
			obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
		}
		this.btn_fontColor.onclick = function() {
			obj.frame.contentDocument.execCommand("foreColor", false, obj.win.prompt("Введите цвет", "#000000"));
			obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
		}
		this.btn_fontBackground.onclick = function() {
			obj.frame.contentDocument.execCommand("hiliteColor", false, obj.win.prompt("Введите цвет", "#ffffff"));
			obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
		}
		this.btn_save.onclick = function() {
			if (!obj.newWin) {
				obj.element.style.display = "block";
				obj.btn_editor.style.display = "block";
				obj.mainContainer.style.display = "none";
				obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
			} else {
				obj.setElementValue(obj.frame.contentDocument.getElementsByTagName("body")[0].innerHTML);
			}
		}
		this.frame.contentDocument.getElementsByTagName("body")[0].onkeyup = function(){
			obj.setElementValue(this.innerHTML);
		};
	}
	createButton(inner,clas,title) {
		var btn = document.createElement("button");
		this.buttonsDiv.appendChild(btn);
		btn.innerHTML = inner;
		btn.setAttribute("type","button");
		btn.setAttribute("title",title);
		btn.setAttribute("class",clas+" htmleditor-btn");
		return btn;
	}
	getElementValue() {
		if (this.element == "textarea")
			return this.element.innerHTML;
		else
			return this.element.value;
	}
	setElementValue(val) {
		if (this.element == "textarea")
			this.element.innerHTML = val;
		else
			this.element.value = val;
	}
	open() {
		this.win = window.open("","_blank","toolbar=0, scrollbars=1, resizable=1, width=" + 1200 + ", height=" + 600);
		this.win.document.write("<html><head><title>"+this.title+" - HTMLEditor</title></head><body></body></html>");
		this.win.document.getElementsByTagName("body")[0].appendChild(this.mainContainer);
		this.mainContainer.style.display = "block";
		this.frame.contentDocument.designMode = "on";
		this.frame.contentDocument.execCommand("styleWithCSS", true, true);
		this.initOnClick();
		this.frame.contentDocument.getElementsByTagName("body")[0].innerHTML = this.getElementValue();
	}
	setHTMLMode(allow) {
		if (allow) {
			this.btn_save.style.display = "inline";
		} else {
			this.btn_save.style.display = "none";
			this.element.style.display = "none";
			this.btn_editor.style.display = "none";
			this.mainContainer.style.display = "block";
		}
	}
	setWidth(w) {
		this.frame.style.width = w;
	}
	setHeight(h) {
		this.frame.style.height = h;
	}
}
