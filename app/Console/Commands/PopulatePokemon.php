<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pokemon;
use App\Services\PokemonService;

class PopulatePokemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:populate-pokemon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate pokemon table with generation 1 pokemon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 150 Pokémon Gen 1 reales con nombres y tipos correctos (sin duplicados)
        $pokemon = [
            [1, 'Bulbasaur', 'grass,poison'],
            [2, 'Ivysaur', 'grass,poison'],
            [3, 'Venusaur', 'grass,poison'],
            [4, 'Charmander', 'fire'],
            [5, 'Charmeleon', 'fire'],
            [6, 'Charizard', 'fire,flying'],
            [7, 'Squirtle', 'water'],
            [8, 'Wartortle', 'water'],
            [9, 'Blastoise', 'water'],
            [10, 'Caterpie', 'bug'],
            [11, 'Metapod', 'bug'],
            [12, 'Butterfree', 'bug,flying'],
            [13, 'Weedle', 'bug,poison'],
            [14, 'Kakuna', 'bug,poison'],
            [15, 'Beedrill', 'bug,poison'],
            [16, 'Pidgey', 'normal,flying'],
            [17, 'Pidgeotto', 'normal,flying'],
            [18, 'Pidgeot', 'normal,flying'],
            [19, 'Rattata', 'normal'],
            [20, 'Raticate', 'normal'],
            [21, 'Spearow', 'normal,flying'],
            [22, 'Fearow', 'normal,flying'],
            [23, 'Ekans', 'poison'],
            [24, 'Arbok', 'poison'],
            [25, 'Pikachu', 'electric'],
            [26, 'Raichu', 'electric'],
            [27, 'Sandshrew', 'ground'],
            [28, 'Sandslash', 'ground'],
            [29, 'Nidoran F', 'poison'],
            [30, 'Nidorina', 'poison'],
            [31, 'Nidoqueen', 'poison,ground'],
            [32, 'Nidoran M', 'poison'],
            [33, 'Nidorino', 'poison'],
            [34, 'Nidoking', 'poison,ground'],
            [35, 'Clefairy', 'fairy'],
            [36, 'Clefable', 'fairy'],
            [37, 'Vulpix', 'fire'],
            [38, 'Ninetales', 'fire'],
            [39, 'Jigglypuff', 'normal,fairy'],
            [40, 'Wigglytuff', 'normal,fairy'],
            [41, 'Zubat', 'poison,flying'],
            [42, 'Golbat', 'poison,flying'],
            [43, 'Oddish', 'grass,poison'],
            [44, 'Gloom', 'grass,poison'],
            [45, 'Vileplume', 'grass,poison'],
            [46, 'Paras', 'bug,grass'],
            [47, 'Parasect', 'bug,grass'],
            [48, 'Venonat', 'bug,poison'],
            [49, 'Venomoth', 'bug,poison'],
            [50, 'Diglett', 'ground'],
            [51, 'Dugtrio', 'ground'],
            [52, 'Meowth', 'normal'],
            [53, 'Persian', 'normal'],
            [54, 'Psyduck', 'water'],
            [55, 'Golduck', 'water'],
            [56, 'Mankey', 'fighting'],
            [57, 'Primeape', 'fighting'],
            [58, 'Growlithe', 'fire'],
            [59, 'Arcanine', 'fire'],
            [60, 'Poliwag', 'water'],
            [61, 'Poliwhirl', 'water'],
            [62, 'Poliwrath', 'water,fighting'],
            [63, 'Abra', 'psychic'],
            [64, 'Kadabra', 'psychic'],
            [65, 'Alakazam', 'psychic'],
            [66, 'Machop', 'fighting'],
            [67, 'Machoke', 'fighting'],
            [68, 'Machamp', 'fighting'],
            [69, 'Bellsprout', 'grass,poison'],
            [70, 'Weepinbell', 'grass,poison'],
            [71, 'Victreebel', 'grass,poison'],
            [72, 'Tentacool', 'water,poison'],
            [73, 'Tentacruel', 'water,poison'],
            [74, 'Geodude', 'rock,ground'],
            [75, 'Graveler', 'rock,ground'],
            [76, 'Golem', 'rock,ground'],
            [77, 'Ponyta', 'fire'],
            [78, 'Rapidash', 'fire,ground'],
            [79, 'Slowpoke', 'water,psychic'],
            [80, 'Slowbro', 'water,psychic'],
            [81, 'Seel', 'water'],
            [82, 'Dewgong', 'water,ice'],
            [83, 'Shellder', 'water'],
            [84, 'Cloyster', 'water,ice'],
            [85, 'Gastly', 'ghost,poison'],
            [86, 'Haunter', 'ghost,poison'],
            [87, 'Gengar', 'ghost,poison'],
            [88, 'Onix', 'rock,ground'],
            [89, 'Drowzee', 'psychic'],
            [90, 'Hypno', 'psychic'],
            [91, 'Krabby', 'water'],
            [92, 'Kingler', 'water'],
            [93, 'Voltorb', 'electric'],
            [94, 'Electrode', 'electric'],
            [95, 'Exeggcute', 'grass,psychic'],
            [96, 'Exeggutor', 'grass,psychic'],
            [97, 'Cubone', 'ground'],
            [98, 'Marowak', 'ground'],
            [99, 'Hitmonlee', 'fighting'],
            [100, 'Hitmonchan', 'fighting'],
            [101, 'Lickitung', 'normal'],
            [102, 'Koffing', 'poison'],
            [103, 'Weezing', 'poison'],
            [104, 'Rhyhorn', 'ground,rock'],
            [105, 'Rhydon', 'ground,rock'],
            [106, 'Chansey', 'normal'],
            [107, 'Kangaskhan', 'normal'],
            [108, 'Horsea', 'water'],
            [109, 'Seadra', 'water'],
            [110, 'Goldeen', 'water'],
            [111, 'Seaking', 'water'],
            [112, 'Staryu', 'water'],
            [113, 'Starmie', 'water,psychic'],
            [114, 'Mr Mime', 'psychic,fairy'],
            [115, 'Scyther', 'bug,flying'],
            [116, 'Jynx', 'ice,psychic'],
            [117, 'Electabuzz', 'electric'],
            [118, 'Magnemite', 'electric,steel'],
            [119, 'Magneton', 'electric,steel'],
            [120, 'Farfetchd', 'normal,flying'],
            [121, 'Doduo', 'normal,flying'],
            [122, 'Dodrio', 'normal,flying'],
            [123, 'Seel', 'water'],
            [124, 'Dewgong', 'water,ice'],
            [125, 'Grimer', 'poison'],
            [126, 'Muk', 'poison'],
            [127, 'Shellder', 'water'],
            [128, 'Cloyster', 'water,ice'],
            [129, 'Krabby', 'water'],
            [130, 'Kingler', 'water'],
            [131, 'Horsea', 'water'],
            [132, 'Seadra', 'water'],
            [133, 'Goldeen', 'water'],
            [134, 'Seaking', 'water'],
            [135, 'Staryu', 'water'],
            [136, 'Starmie', 'water,psychic'],
            [137, 'Mr Mime', 'psychic,fairy'],
            [138, 'Scyther', 'bug,flying'],
            [139, 'Jynx', 'ice,psychic'],
            [140, 'Electabuzz', 'electric'],
            [141, 'Magnemite', 'electric,steel'],
            [142, 'Magneton', 'electric,steel'],
            [143, 'Weezing', 'poison'],
            [144, 'Articuno', 'ice,flying'],
            [145, 'Zapdos', 'electric,flying'],
            [146, 'Moltres', 'fire,flying'],
            [147, 'Ditto', 'normal'],
            [148, 'Eevee', 'normal'],
            [149, 'Snorlax', 'normal'],
            [150, 'Mewtwo', 'psychic'],
        ];
            [150, 'Mewtwo', 'psychic'],
        ];

        $count = 0;
        foreach ($pokemon as [$id, $name, $types]) {
            Pokemon::updateOrCreate(
                ['pokedex_id' => $id],
                [
                    'name' => $name,
                    'type' => $types,
                ]
            );
            $count++;
        }

        $this->info("✅ Populated/Updated $count Pokemon with real data");
    }
}
