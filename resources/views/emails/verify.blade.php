<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <style>
        .emailContainer{
            background-color: #ccc;
            padding: 20px;
        }
        .emailWrapper{
            padding: 10px 20px;
            margin: auto;
            text-align: left;
            width: 70%;
            background-color: #fff;
        }
        .emailHeader{
            object-fit: contain;
            object-fit: contain;
            margin: 20px auto;
            display: block;
        }

        .emailButton{
            display: block;
            border: 1px solid transparent;
            padding: 15px 30px;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            width: max-content;
            align-self: center;
            margin: 20px 0;
            text-transform: uppercase;
            margin: auto
        }
        .success{
            background-color: green;
        }
        .danger{
            background-color: #c50000
        }
        .emailFooter{
            text-align: center;
        }
        .emailFooter ul{
            margin: auto;
            padding: 0
        }
        .emailFooter ul li{
            display: inline-block;
            list-style-type: none
        }
        .emailFooter ul li img{
            display: block;
            width: 50px;
            height: 50px;
        }
    </style>
</head>
<body>
    <section class="emailContainer">
        <div class="emailWrapper">
            <img class="emailHeader" src="https://i.ibb.co/TWc5Rc1/Colored-Horizontal-Version3.png" alt="Colored-Horizontal-Version3">
            <p>Hi, Please help us in verifing {{$user->firstName .' ' .$user->lastName}} account for saving our privacy.</p>
            <p>If you are sure that the owner of the requested email is a member of our crew, so please activate the account.</p>
            <a href="https://evaluation-system.ieeehsb.org/verify/{{ $confirmation_code }}" target="_blank" class="emailButton success"> Activate Account </a>
            {{-- <a href="{{ URL::to('api/register/verify/' . $confirmation_code) }}" target="_blank">Active This Account</a> --}}
            <p>If this is an anonymous or unsure user, you can delete this account but please check with your manager before performing this operation</p>
            <a href="https://evaluation-system.ieeehsb.org/change-user/{{$user->id}}" target="_blank" class="emailButton danger">Delete account</a>
           {{--  <a href="{{URL::to('api/change-user/' . encrypt($user->id)) }}" >Delete</a> --}}
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>E-Mail</th>
                        @if($type == 'volunteer')
                        <th>Position</th>
                        @if($role->name=='ex_com')
                            <th>EX Options</th>
                        @else
                            <th>Committee</th>
                        @endif
                      @endif
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{$user->firstName .' ' .$user->lastName }}</td>
                        <td>{{$user->email}}</td>
                        @if($type == 'volunteer')

                        <td>{{$role->name}}</td>

                        @if($role->name == 'ex_com')

                            <td>{{\App\Position::find($req->ex_options)->name}}</td>
                        @elseif($role->name == 'highboard')
                            <td>{{$pos}}</td>

                                <td>{{\App\Committee::find($req->committee)->name}}</td>
                            @else
                                <td>{{\App\Committee::find($req->committee)->name}}</td>

                            @endif
                      @endif
                    </tr>
                </tbody>
            </table>
            <p>If you have any problems, feel free to contact with us <a href="mailto:hr.admin@ieeehsb.org">hr.admin@ieeehsb.org</a>.</p>
            <footer class="emailFooter">
                Follow US
                <ul>
                    <li><a href="https://www.facebook.com/ieeehsb/"><img src="https://i.ibb.co/3dWpRYM/facebook.png" alt="facebook"></a></li>
                    <li><a href="https://www.instagram.com/ieee_hsb/"><img src="https://i.ibb.co/F4Ptw5S/instagram.png" alt="instagram"></a></li>
                </ul>
            </footer>
        </div>
    </section>
</body>
</html>
