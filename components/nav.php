<?php
if (!defined('IndexAccessed')) {
    die('Direct access not permitted');
}

/** @TODO
 *  Automate navbar for standings, schedule, and teams based on database values
 */
?>
<nav class='navbar'>
    <button name='navbar-ham' aria-label='Dropdown Menu' class='navbar-ham'>
        <svg viewBox="0 0 26 26" width="26" height="26">
            <rect width="26" height="4" stroke="white" fill="white"></rect>
            <rect y="10" width="26" height="4" stroke="white" fill="white"></rect>
            <rect y="20" width="26" height="4" stroke="white" fill="white"></rect>
        </svg>
    </button>
    <ul class='navbar-container'>
        <li class='navbar-elem'><a href='/news'>News</a></li>
        <li class='navbar-elem'>
            <a href='javascript:'>Standings</a>
            <ul class='navbar-dropdown d1'>
                <li class='navbar-elem'><a href='/standings/winter2021'>Winter 2021</a></li>
                <li class='navbar-elem'><a href='/standings/summer2021'>Summer 2021</a></li>
            </ul>
        </li>
        <li class='navbar-elem'>
            <a href='javascript:'>Schedule</a>
            <ul class='navbar-dropdown d1'>
                <li class='navbar-elem'><a href='/schedule/winter2021'>Winter 2021</a></li>
                <li class='navbar-elem'><a href='/schedule/summer2021'>Summer 2021</a></li>
            </ul>
        </li>
        <li class='navbar-elem'>
            <a href='javascript:'>Teams</a>
            <ul class='navbar-dropdown d1'>
                <li class='navbar-elem'>
                    <a href='javascript:'>Winter 2021</a>
                    <ul class='navbar-dropdown d2'>
                        <li class='navbar-elem'><a href='/rosters/wolves-winter2021'>Grey Wolves</a></li>
                        <li class='navbar-elem'><a href='/rosters/hawks-winter2021'>Red Hawks</a></li>
                        <li class='navbar-elem'><a href='/rosters/bears-winter2021'>Black Bears</a></li>
                        <li class='navbar-elem'><a href='/rosters/eagles-winter2021'>Golden Eagles</a></li>
                    </ul>
                </li>
                <li class='navbar-elem'>
                    <a href='javascript:'>Summer 2021</a>
                    <ul class='navbar-dropdown d2'>
                        <li class='navbar-elem'><a href='wolves-summer2021'>Grey Wolves</a></li>
                        <li class='navbar-elem'><a href='skyhawks-summer2021'>Red Skyhawks</a></li>
                        <li class='navbar-elem'><a href='bears-summer2021'>Black Bears</a></li>
                    </ul>
                </li>
            </ul>
        </li>
        <li class='navbar-elem'><a href='/gallery'>Gallery</a></li>
    </ul>
</nav>