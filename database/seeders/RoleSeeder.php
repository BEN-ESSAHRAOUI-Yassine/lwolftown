<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            // Village (18)
            [
                'key' => 'villager',
                'description' => 'The Villager has no special power but participates in daytime votes to eliminate suspected werewolves.',
                'faction' => 'village',
                'night_order' => null,
                'abilities' => null,
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'seer',
                'description' => 'The Seer can inspect one player each night to learn their faction (village, werewolf, or neutral). The village\'s most important investigative role.',
                'faction' => 'village',
                'night_order' => 10,
                'abilities' => ['inspect'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'witch',
                'description' => 'The Witch has two potions: a healing potion that can save a player from werewolf attack, and a poison potion that can eliminate a player. Each can only be used once per game.',
                'faction' => 'village',
                'night_order' => 11,
                'abilities' => ['save_potion', 'poison_potion'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'hunter',
                'description' => 'When the Hunter dies (unless silenced), they may choose one player to eliminate with their final shot.',
                'faction' => 'village',
                'night_order' => null,
                'abilities' => ['last_shot'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'bodyguard',
                'description' => 'The Bodyguard protects one player each night from werewolf attacks. The same player cannot be protected two nights in a row.',
                'faction' => 'village',
                'night_order' => 8,
                'abilities' => ['protect'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'little_girl',
                'description' => 'The Little Girl can peek during the werewolf phase to see who the werewolves are, but risks being caught if they look too long.',
                'faction' => 'village',
                'night_order' => 9,
                'abilities' => ['spy'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'cupid',
                'description' => 'On the first night, Cupid links two players as lovers. If one lover dies, the other dies of grief. If both survive, they share a special victory.',
                'faction' => 'village',
                'night_order' => 0,
                'abilities' => ['link'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'elder',
                'description' => 'The Elder has survived many attacks and can withstand the first werewolf attack. However, once their resilience is broken, their abilities are weakened.',
                'faction' => 'village',
                'night_order' => null,
                'abilities' => ['resilience', 'fragility'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'scapegoat',
                'description' => 'When votes are tied, the Scapegoat is eliminated instead. On their death, they can choose players who may not vote the next day.',
                'faction' => 'village',
                'night_order' => null,
                'abilities' => ['sacrifice', 'last_decree'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'village_idiot',
                'description' => 'The Village Idiot can never be eliminated by village vote. When they receive the most votes, they are revealed as innocent instead.',
                'faction' => 'village',
                'night_order' => null,
                'abilities' => ['revealed_innocence'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'two_sisters',
                'description' => 'The Two Sisters are linked siblings. They know each other\'s identity and win or lose together as part of the village.',
                'faction' => 'village',
                'night_order' => null,
                'abilities' => ['kinship'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'three_brothers',
                'description' => 'The Three Brothers are linked siblings. They know each other\'s identity and win or lose together as part of the village.',
                'faction' => 'village',
                'night_order' => null,
                'abilities' => ['kinship'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'stuttering_judge',
                'description' => 'The Stuttering Judge can call for a second vote during the day phase once per game, giving the village another chance to decide.',
                'faction' => 'village',
                'night_order' => null,
                'abilities' => ['second_vote'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'knight_with_rusty_sword',
                'description' => 'The Knight carries a rusty sword. If eliminated by werewolves, the rust infects one random werewolf, who dies the following night.',
                'faction' => 'village',
                'night_order' => null,
                'abilities' => ['rusty_wound'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'devoted_servant',
                'description' => 'Before the game starts, the Devoted Servant may choose a player to follow. If that player dies, the Devoted Servant takes over their role.',
                'faction' => 'village',
                'night_order' => null,
                'abilities' => ['pre_submit_swap'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'bear_tamer',
                'description' => 'The Bear Tamer has a bear that growls if a werewolf is sitting next to them. The village is notified if the bear growls.',
                'faction' => 'village',
                'night_order' => 14,
                'abilities' => ['bear_growl'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'fox',
                'description' => 'The Fox can sniff one player each night to detect if they are a werewolf. If the Fox is wrong, they lose their ability for the rest of the game.',
                'faction' => 'village',
                'night_order' => 13,
                'abilities' => ['sniff'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],
            [
                'key' => 'the_master',
                'description' => 'The Master can enslave one player at the start of the game, forcing them to obey the Master\'s commands or face consequences.',
                'faction' => 'village',
                'night_order' => 0,
                'abilities' => ['enslave'],
                'win_condition' => 'All werewolves must be eliminated.',
            ],

            // Werewolves (6)
            [
                'key' => 'werewolf',
                'description' => 'The Werewolf is a basic member of the wolf pack. They vote with other werewolves each night to choose a villager to eliminate.',
                'faction' => 'werewolves',
                'night_order' => 5,
                'abilities' => ['group_kill'],
                'win_condition' => 'Werewolves achieve voting parity with the village.',
            ],
            [
                'key' => 'big_bad_wolf',
                'description' => 'The Big Bad Wolf is a stronger werewolf who can make an extra kill. If no other werewolves remain, the Big Bad Wolf acts alone.',
                'faction' => 'werewolves',
                'night_order' => 6,
                'abilities' => ['extra_kill'],
                'win_condition' => 'Werewolves achieve voting parity with the village.',
            ],
            [
                'key' => 'accursed_wolf_father',
                'description' => 'The Accursed Wolf Father can convert one villager into a werewolf, but can only do this once per game. This creates a new werewolf ally.',
                'faction' => 'werewolves',
                'night_order' => 4,
                'abilities' => ['convert'],
                'win_condition' => 'Werewolves achieve voting parity with the village.',
            ],
            [
                'key' => 'white_werewolf',
                'description' => 'The White Werewolf is a lone wolf who kills other werewolves at night. They win by being the last one standing.',
                'faction' => 'werewolves',
                'night_order' => 7,
                'abilities' => ['solo_kill'],
                'win_condition' => 'White Werewolf wins by being the last player alive.',
            ],
            [
                'key' => 'wolf_hound',
                'description' => 'The Wolf Hound is a conflicted creature who can choose to join either the werewolves or the village. Their faction changes based on their choice.',
                'faction' => 'werewolves',
                'night_order' => 3,
                'abilities' => ['choose_side'],
                'win_condition' => 'Wolf Hound wins with the faction they choose to join.',
            ],
            [
                'key' => 'silencer',
                'description' => 'The Silencer can silence one player each night, preventing them from speaking or voting during the next day phase.',
                'faction' => 'werewolves',
                'night_order' => 2,
                'abilities' => ['silence'],
                'win_condition' => 'Werewolves achieve voting parity with the village.',
            ],

            // Neutral (3)
            [
                'key' => 'angel',
                'description' => 'The Angel wins if they are eliminated by village vote during the first day. They have no night ability and must convince the village to vote them out.',
                'faction' => 'neutral',
                'night_order' => null,
                'abilities' => ['divine_favor'],
                'win_condition' => 'Angel wins if eliminated by village vote on Day 1.',
            ],
            [
                'key' => 'pied_piper',
                'description' => 'The Pied Piper enchants one player each night. They win when all living players have been enchanted by their music.',
                'faction' => 'neutral',
                'night_order' => 12,
                'abilities' => ['enchant'],
                'win_condition' => 'Pied Piper wins when all living players are enchanted.',
            ],
            [
                'key' => 'kira',
                'description' => 'Kira can guess a player\'s role each night. They win by correctly identifying three different roles without being eliminated.',
                'faction' => 'neutral',
                'night_order' => 15,
                'abilities' => ['role_guess'],
                'win_condition' => 'Kira wins by correctly guessing 3 player roles.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['key' => $role['key']],
                $role
            );
        }
    }
}
